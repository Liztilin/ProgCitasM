<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_cita = $_POST['id_cita'] ?? null;
$nuevo_horario = $_POST['nuevo_horario'] ?? null;

// Validar datos
if (!$id_cita || !$nuevo_horario) {
    die("Datos inválidos.");
}

// Verificar que la cita le pertenezca
$stmt = $conn->prepare("SELECT fecha, id_centro FROM cita WHERE id_cita = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_cita, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Cita no encontrada o no autorizada.");
}

$cita = $result->fetch_assoc();
$fecha = $cita['fecha'];
$id_centro = $cita['id_centro'];

// Verificar si el horario ya está ocupado
$stmt2 = $conn->prepare("SELECT COUNT(*) AS total FROM cita WHERE fecha = ? AND horario = ? AND id_centro = ? AND estado = 'pendiente'");
$stmt2->bind_param("ssi", $fecha, $nuevo_horario, $id_centro);
$stmt2->execute();
$res = $stmt2->get_result();
$row = $res->fetch_assoc();

if ($row['total'] > 0) {
    header("Location: citas.php?error=ocupado");
    exit;
}

// Actualizar horario
$stmt3 = $conn->prepare("UPDATE cita SET horario = ? WHERE id_cita = ?");
$stmt3->bind_param("si", $nuevo_horario, $id_cita);
if ($stmt3->execute()) {
    header("Location: citas.php?reprogramada=1");
    exit;
} else {
    echo "Error al guardar cambios: " . $conn->error;
}
