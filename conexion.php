<?php
$es_local = strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false;

if ($es_local) {
    // Conexión en entorno local (XAMPP con puerto 3308)
    $host = "127.0.0.1";
    $usuario = "root";
    $clave = "";
    $base_de_datos = "sanacita";
    $puerto = 3308;
    $conn = new mysqli($host, $usuario, $clave, $base_de_datos, $puerto);
} else {
    // Conexión en servidor remoto
    $host = "localhost";
    $usuario = "u143755789_admin"; 
    $clave = "Sanacita1234!"; 
    $base_de_datos = "u143755789_Sanacita"; 
    $conn = new mysqli($host, $usuario, $clave, $base_de_datos);
}

// Verificar conexión
if ($conn->connect_error) {
    die("Error en la conexión a la base de datos: " . $conn->connect_error);
}
?>
