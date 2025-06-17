<?php
$host = "localhost";
$user = "root";        // usuario por defecto en XAMPP
$pass = "";            // contraseña por defecto en XAMPP es vacía
$db   = "estetica";    // tu base de datos

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
