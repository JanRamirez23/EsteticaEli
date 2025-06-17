<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "12345", "estetica");
if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión']));
}

$result = $conn->query("SELECT id, nombre FROM servicios");
$servicios = [];

while ($row = $result->fetch_assoc()) {
    $servicios[] = $row;
}

echo json_encode($servicios);
$conn->close();
