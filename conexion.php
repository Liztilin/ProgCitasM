<?php
$host = "localhost";
$usuario = "root"; 
$clave = "1234"; 
$base_de_datos = "Sanacita"; 

$conn = new mysqli($host, $usuario, $clave, $base_de_datos);

if ($conn->connect_error) {
    die("Error en la conexión a la base de datos: " . $conn->connect_error);
}
?>
