<?php
session_start();

$host = 'localhost';
$user = '';
$password = '';
$database = '';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd połączenia z bazą']);
    exit;
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

// GET - pobieranie wszystkich zadań
if ($method === 'GET') {
    $result = $conn->query("SELECT * FROM tasks");
    $tasks = [];

    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    echo json_encode($tasks);
    exit;
}

// POST - dodanie nowego zadania lub aktualizacja zadania
if ($method === 'POST') {
    // Aktualizacja zadania
    if (isset($data['action']) && $data['action'] === 'update') {
        if (!isset($data['id'], $data['type'], $data['text'], $data['short'], $data['assignedTo'], $data['done'])) {
            echo json_encode(['success' => false, 'error' => 'Brak wymaganych danych do aktualizacji']);
            exit;
        }

        $id = (int)$data['id'];
        $type = $conn->real_escape_string($data['type']);
        $text = $conn->real_escape_string($data['text']);
        $short = $conn->real_escape_string($data['short']);
        $assignedTo = $conn->real_escape_string($data['assignedTo']);
        $done = (int)$data['done'];

        $stmt = $conn->prepare("UPDATE tasks SET type = ?, text = ?, short = ?, assigned_to = ?, done = ? WHERE id = ?");
        $stmt->bind_param("ssssii", $type, $text, $short, $assignedTo, $done, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        exit;
    }

    // Dodanie nowego zadania
    if (!isset($data['date'], $data['type'], $data['text'])) {
        echo json_encode(['success' => false, 'error' => 'Brak wymaganych danych']);
        exit;
    }

    $date = $conn->real_escape_string($data['date']);
    $type = $conn->real_escape_string($data['type']);
    $text = $conn->real_escape_string($data['text']);
    $short = isset($data['short']) ? $conn->real_escape_string($data['short']) : null;
    $assignedTo = isset($data['assignedTo']) ? $conn->real_escape_string($data['assignedTo']) : null;
    $done = isset($data['done']) ? (int)$data['done'] : 0;

    $stmt = $conn->prepare("INSERT INTO tasks (date, type, text, short, assigned_to, done) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $date, $type, $text, $short, $assignedTo, $done);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    exit;
}

// DELETE - usuwanie zadania po short i date
if ($method === 'DELETE') {
    if (!isset($data['date'], $data['short'])) {
        echo json_encode(['success' => false, 'error' => 'Brak danych do usunięcia']);
        exit;
    }

    $date = $conn->real_escape_string($data['date']);
    $short = $conn->real_escape_string($data['short']);

    $stmt = $conn->prepare("DELETE FROM tasks WHERE date = ? AND short = ?");
    $stmt->bind_param("ss", $date, $short);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

// PUT - aktualizacja zadania (done + opcjonalnie inne dane)
if ($method === 'PUT') {
    if (!isset($data['date'], $data['short'])) {
        echo json_encode(['success' => false, 'error' => 'Brak wymaganych danych do aktualizacji']);
        exit;
    }

    $date = $conn->real_escape_string($data['date']);
    $short = $conn->real_escape_string($data['short']);
    $done = isset($data['done']) ? (int)$data['done'] : 0;
    $type = isset($data['type']) ? $conn->real_escape_string($data['type']) : '';
    $text = isset($data['text']) ? $conn->real_escape_string($data['text']) : '';
    $assignedTo = isset($data['assignedTo']) ? $conn->real_escape_string($data['assignedTo']) : '';

    $stmt = $conn->prepare("UPDATE tasks SET done = ?, type = ?, text = ?, assigned_to = ? WHERE date = ? AND short = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Błąd przygotowania zapytania: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("isssss", $done, $type, $text, $assignedTo, $date, $short);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Błąd wykonania zapytania: ' . $stmt->error]);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

// Jeżeli metoda nie jest obsługiwana
echo json_encode(['error' => 'Nieobsługiwana metoda']);
exit; 