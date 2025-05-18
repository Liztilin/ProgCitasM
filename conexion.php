<?php
$host = "localhost";
$usuario = "u143755789_admin"; 
$clave = "Sanacita1234!"; 
$base_de_datos = "u143755789_Sanacita"; 

$conn = new mysqli($host, $usuario, $clave, $base_de_datos);

if ($conn->connect_error) {
    die("Error en la conexión a la base de datos: " . $conn->connect_error);
}
?>
