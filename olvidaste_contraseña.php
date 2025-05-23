<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'libs/phpmailer/PHPMailer.php';
require 'libs/phpmailer/SMTP.php';
require 'libs/phpmailer/Exception.php';


$mensaje = '';

function enviarCorreoRecuperacion($email, $token) {
    $mail = new PHPMailer(true);
    try {
        // Configuración GMAIL
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sanacita.contacto@gmail.com';    // CAMBIAR ESTO
        $mail->Password   = 'gxnpdsxwwzzicbzj'; // CAMBIAR ESTO
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('sanacita.contacto@gmail.com', 'Sanacita');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Código de recuperación';

        $mail->Body = "
            <html>
            <body>
                <h2>Código de recuperación</h2>
                <p>Tu código para restablecer la contraseña es:</p>
                <h3 style='color: #2196F3;'>$token</h3>
                <p>Ingresa este código en la página de recuperación.</p>
                <p><em>El código expirará en 15 minutos</em></p>
            </body>
            </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $mensaje = '<div>Ingresa tu correo electrónico</div>';
    } else {
        try {
            $conn->begin_transaction();
            
            // Verificar si el correo existe
            $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();
                $token = strtoupper(bin2hex(random_bytes(3))); // Genera un código de 6 caracteres

                // Insertar token en la base de datos
                $stmt = $conn->prepare("INSERT INTO token_recuperacion (id_usuario, token, fecha_expiracion) 
                                      VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
                $stmt->bind_param("is", $usuario['id_usuario'], $token);
                
                if ($stmt->execute() && enviarCorreoRecuperacion($email, $token)) {
                    $conn->commit();
                    $_SESSION['email_recuperacion'] = $email; // Guardar email en sesión
                    header('Location: ingresar_token.php'); // Redirigir a página de ingreso
                    exit();
                } else {
                    $conn->rollback();
                    $mensaje = '<div>Error al enviar el código. Intenta nuevamente.</div>';
                }
            } else {
                $mensaje = '<div>El correo no está registrado</div>';
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en la base de datos: " . $e->getMessage());
            $mensaje = '<div>Error interno del sistema</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Sanacita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="olvidaste_contraseña.css">
</head>
<body>
    <div class="medical-wrapper">
        <div class="medical-card">
            <div class="medical-header">
                <div class="medical-logo">
                    <img src="Imagenes progsanacita/logo_sanacita2.png" alt="Sanacita Logo">
                </div>
                <h1 class="medical-title">
                    <span>Recuperar Acceso</span>
                    <small>Sanacita</small>
                </h1>
            </div>

            <form method="POST" class="medical-form">
                <div class="input-container">
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="correo@sanacita.com"
                           required
                           autocomplete="email">
                    <svg class="input-icon" viewBox="0 0 24 24">
                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm-8-7L4 6h16l-8 5z"/>
                    </svg>
                </div>

                <button type="submit" class="medical-btn">
                    Enviar Instrucciones
                    <svg class="btn-icon" viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </form>

            <div class="medical-footer">
                <div class="divider"></div>
                <p class="login-link">¿Recordó su contraseña? <a href="login.php">Iniciar Sesion</a></p>
            </div>
        </div>
        
        <!-- Elementos decorativos de fondo -->
        <div class="deco-circle deco-1"></div>
        <div class="deco-circle deco-2"></div>
        <div class="deco-wave"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>