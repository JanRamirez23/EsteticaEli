<?php
$servername = "localhost";  // IP o nombre del servidor
$username = "root";         // Tu nombre de usuario de la base de datos
$password = "";         // Tu contraseña de la base de datos
$dbname = "estetica";              // Nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
//echo "Conexión exitosa";
?>
