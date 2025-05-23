<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_cita = $_GET['id_cita'] ?? null;

if ($id_cita) {
    // Obtener información de la cita
    $sql = "SELECT fecha, horario, id_centro FROM cita WHERE id_cita = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_cita, $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $cita = $resultado->fetch_assoc();
        $fecha = $cita['fecha'];
        $horario = $cita['horario'];
        $id_centro = $cita['id_centro'];

        // Cancelar la cita (marcar como cancelada)
        $stmt_cancelar = $conn->prepare("UPDATE cita SET estado = 'cancelada' WHERE id_cita = ?");
        $stmt_cancelar->bind_param("i", $id_cita);
        $stmt_cancelar->execute();

        // Buscar otros usuarios con citas más tarde en ese mismo centro y fecha
        $sql2 = "SELECT id_usuario FROM cita 
                 WHERE fecha = ? AND id_centro = ? AND horario > ? AND estado = 'pendiente'";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("sis", $fecha, $id_centro, $horario);
        $stmt2->execute();
        $usuarios_disponibles = $stmt2->get_result();

        // Enviar notificaciones a usuarios
        $titulo = "¡Cita disponible!";
        $mensaje = "Se liberó una cita para el $fecha a las $horario. Puedes reprogramar tu cita si lo deseas.";

        while ($usuario = $usuarios_disponibles->fetch_assoc()) {
            $id_notificado = $usuario['id_usuario'];
            $stmt_noti = $conn->prepare("INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo) VALUES (?, ?, ?, 'oportunidad')");
            $stmt_noti->bind_param("iss", $id_notificado, $titulo, $mensaje);
            $stmt_noti->execute();
        }

        header("Location: citas.php?cancelada=1");
        exit;
    } else {
        echo "No se encontró la cita o no tienes permiso para cancelarla.";
    }
} else {
    echo "Solicitud inválida.";
}
?>
