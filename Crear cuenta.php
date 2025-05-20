<?php
session_start();
require 'conexion.php';

$mensaje = '';

function enviarCorreoRecuperacion($email, $token) {
    $asunto = "Recuperación de contraseña";
    $enlace = "http://" . $_SERVER['HTTP_HOST'] . "/nueva_contrasena.php?token=$token";
    $mensajeCorreo = "Hola,\n\nPara restablecer tu contraseña, haz clic en el siguiente enlace:\n\n$enlace\n\nEste enlace expirará en 1 hora.\n\nSi no solicitaste este cambio, ignora este mensaje.";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "From: Recuperación <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
    $headers .= "Reply-To: soporte@" . $_SERVER['HTTP_HOST'] . "\r\n";

    return mail($email, $asunto, $mensajeCorreo, $headers);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $mensaje = '<div class="error">Por favor ingresa tu correo electrónico</div>';
    } else {
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $token = bin2hex(random_bytes(32));
            $expiracion = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            $stmt2 = $conn->prepare("UPDATE usuario SET token_recuperacion = ?, token_expiracion = ? WHERE email = ?");
            $stmt2->bind_param("sss", $token, $expiracion, $email);
            
            if ($stmt2->execute()) {
                if (enviarCorreoRecuperacion($email, $token)) {
                    $mensaje = '<div class="success">Se ha enviado un enlace de recuperación a tu correo electrónico. Revisa tu bandeja de entrada.</div>';
                } else {
                    $mensaje = '<div class="error">Error al enviar el correo. Por favor intenta nuevamente.</div>';
                }
            } else {
                $mensaje = '<div class="error">Error al procesar tu solicitud. Intenta nuevamente.</div>';
            }
            $stmt2->close();
        } else {
            $mensaje = '<div class="error">El correo electrónico no está registrado</div>';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="olvidaste_contraseña.css">
</head>
<body>
    <div class="recovery-container">
        <h1>Recuperar Contraseña</h1>

        <?php if (!empty($mensaje)) echo $mensaje; ?>

        <form class="recovery-form" method="POST" action="">
            <label for="email">Correo electrónico registrado</label>
            <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com">

            <button type="submit">Enviar enlace de recuperación</button>
        </form>

        <p class="login-link">¿Recordaste tu contraseña? <a href="login.php">Iniciar sesión</a></p>
    </div>
</body>
</html>
