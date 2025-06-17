<?php
header('Content-Type: application/json');
require 'conexion.php';  // incluye tu archivo de conexión

$sql = "SELECT id, nombre FROM servicios";
$result = $conn->query($sql);

$servicios = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $servicios[] = $row;
    }
}

echo json_encode($servicios);

$conn->close();
?>
    