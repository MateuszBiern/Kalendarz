<?php
$host = 'localhost';
$user = '';
$password = '';
$database = 'kalendarz';


$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Błąd połączenia: ' . $conn->connect_error);
}
echo 'Połączenie udane!';
?>
