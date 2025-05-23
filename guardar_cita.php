<?php
session_start();
require 'libs/phpmailer/PHPMailer.php';
require 'libs/phpmailer/SMTP.php';
require 'libs/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

if (!isset($_SESSION['id_usuario'])) {
    echo "Acceso no autorizado.";
    exit;
}

require_once 'conexion.php';

$id_usuario = $_SESSION['id_usuario'];
$centro = $_POST['centro'];
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$medio = $_POST['medio'];

// Validar campos vacíos
if (empty($centro) || empty($fecha) || empty($hora) || empty($medio)) {
    echo "Faltan datos.";
    exit;
}

// Verificar si ese horario ya está ocupado
$sql_check = "SELECT COUNT(*) AS total FROM cita WHERE fecha = ? AND horario = ? AND id_centro = ?";
$check_stmt = $conn->prepare($sql_check);
$check_stmt->bind_param("ssi", $fecha, $hora, $centro);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$row = $check_result->fetch_assoc();

if ($row['total'] > 0) {
    header("Location: citas.php?error=1");
    exit;
}
$check_stmt->close();

// Insertar la nueva cita
$sql = "INSERT INTO cita (id_usuario, id_centro, fecha, horario, medio_notificacion)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iisss", $id_usuario, $centro, $fecha, $hora, $medio);

if ($stmt->execute()) {

    // 📩 Obtener correo y nombre del usuario
    $stmt_user = $conn->prepare("SELECT email, nombre FROM usuario WHERE id_usuario = ?");
    $stmt_user->bind_param("i", $id_usuario);
    $stmt_user->execute();
    $res_user = $stmt_user->get_result();
    $usuario = $res_user->fetch_assoc();
    $stmt_user->close();

    $correo_usuario = $usuario['email'];
    $nombre_usuario = $usuario['nombre'];

    // 📌 Obtener nombre del centro
    $stmt_centro = $conn->prepare("SELECT nombre_centro FROM centro_salud WHERE id_centro = ?");
    $stmt_centro->bind_param("i", $centro);
    $stmt_centro->execute();
    $res_centro = $stmt_centro->get_result();
    $nombre_centro = $res_centro->fetch_assoc()['nombre_centro'];
    $stmt_centro->close();


    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sanacita.contacto@gmail.com'; // tu correo
        $mail->Password   = 'gxnp dsxw wzzi cbzj';  // tu contraseña de aplicación
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('sanacita.contacto@gmail.com', 'Sanacita');
        $mail->addAddress($correo_usuario, $nombre_usuario);

        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de Cita - Sanacita';
        $mail->Body    = "
            <h3>Hola, $nombre_usuario</h3>
            <p>Tu cita ha sido registrada con éxito.</p>
            <p><strong>Centro de Salud:</strong> $nombre_centro<br>
               <strong>Fecha:</strong> $fecha<br>
               <strong>Hora:</strong> $hora</p>
            <p>Gracias por usar <strong>Sanacita</strong>.</p>
        ";

        $mail->send();
        // echo "Correo enviado con éxito";
    } catch (Exception $e) {
        // Opcional: loguear error o mostrar mensaje de advertencia
        // echo "Error al enviar el correo: {$mail->ErrorInfo}";
    }

    // ✅ Redirigir con éxito
    header("Location: citas.php?success=1");
    exit;
} else {
    echo "Error al guardar la cita: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
