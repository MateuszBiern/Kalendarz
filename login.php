<?php
session_start();

$host = 'localhost';
$user = '';
$password = '';
$database = 'kalendarz';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($password === $row['password_hash']) {
            $_SESSION['user_id'] = $row['id'];
            header('Location: index.php');
            exit;
        } else {
            $error = "Niepoprawne hasło.";
        }
    } else {
        $error = "Niepoprawny login.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <h2>Logowanie</h2>
    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label>Login: <input type="text" name="username" required></label><br>
        <label>Hasło: <input type="password" name="password" required></label><br>
        <button type="submit">Zaloguj się</button>
    </form>
</body>
</html>
