<?php
require_once 'conexion.php';

$fecha = $_GET['fecha'] ?? '';
$centro = $_GET['centro'] ?? '';

$horarios_posibles = [
    '09:00:00', '10:00:00', '11:00:00', '12:00:00',
    '13:00:00',
    '15:00:00', '16:00:00', '17:00:00'
];

$horarios_ocupados = [];

if ($fecha && $centro) {
    $stmt = $conn->prepare("SELECT horario FROM cita WHERE fecha = ? AND id_centro = ?");
    $stmt->bind_param("si", $fecha, $centro);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $horarios_ocupados[] = $row['horario'];
    }

    $stmt->close();
}

// Calcular disponibles
$disponibles = array_diff($horarios_posibles, $horarios_ocupados);

// Devolver como JSON
header('Content-Type: application/json');
echo json_encode(array_values($disponibles));
?>
