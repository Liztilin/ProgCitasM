<?php
session_start();
require 'conexion.php';

$mensaje = '';
$email = $_SESSION['email_recuperacion'] ?? '';

// Validación del token ingresado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $token = strtoupper(trim($_POST['token']));

    if (!empty($token) && preg_match('/^[A-Z0-9]{6}$/', $token) && !empty($email)) {
        try {
            $stmt = $conn->prepare("SELECT t.token 
                                   FROM token_recuperacion t
                                   JOIN usuario u ON t.id_usuario = u.id_usuario
                                   WHERE u.email = ? AND t.token = ? AND t.fecha_expiracion > NOW()");
            $stmt->bind_param("ss", $email, $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $_SESSION['token_valido'] = true;
                header('Location: nueva_contra.php');
                exit();
            } else {
                $mensaje = '<div class="medical-alert">Código inválido o expirado</div>';
            }
        } catch (Exception $e) {
            error_log("Error validando token: " . $e->getMessage());
            $mensaje = '<div class="medical-alert">Error al validar el código</div>';
        }
    } else {
        $mensaje = '<div class="medical-alert">El código debe tener exactamente 6 caracteres alfanuméricos</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar Código - Sanacita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="ingresar_token.css">
</head>
<body>
    <div class="medical-wrapper">
        <div class="medical-card">
            <div class="medical-header">
                <div class="medical-logo">
                    <img src="imagenes progsanacita/logo_sanacita2.png" alt="Sanacita Logo">
                </div>
                <h1 class="medical-title">
                    <span>Verificación de Código</span>
                    <small>Sistema Médico Sanacita</small>
                </h1>
            </div>

            <form method="POST" class="medical-form" id="tokenForm">
                <?php if (!empty($mensaje)) echo $mensaje; ?>

                <div class="input-container">
                    <input type="text" 
                           name="token" 
                           id="token"
                           placeholder="ABC123"
                           maxlength="6"
                           pattern="[A-Z0-9]{6}"
                           required
                           class="form-control text-uppercase"
                           autocomplete="off">
                    <svg class="input-icon" viewBox="0 0 24 24">
                        <path d="M12 17a2 2 0 0 0 2-2 2 2 0 0 0-2-2 2 2 0 0 0-2 2 2 2 0 0 0 2 2m6-9a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2h1V6a5 5 0 0 1 5-5 5 5 0 0 1 5 5v2h1m-6-2a3 3 0 0 0-3 3v2h6V6a3 3 0 0 0-3-3z"/>
                    </svg>
                </div>

                <button type="submit" class="medical-btn">
                    Verificar Código
                    <svg class="btn-icon" viewBox="0 0 24 24">
                        <path d="M10 17l5-5-5-5v10z"/>
                    </svg>
                </button>
            </form>

            <div class="medical-footer">
                <div class="divider"></div>
                <p class="login-link">
                    ¿Recordaste tu contraseña? <a href="login.php">Iniciar sesión</a>
                </p>
            </div>
        </div>

        <!-- Elementos decorativos -->
        <div class="deco-circle deco-1"></div>
        <div class="deco-circle deco-2"></div>
        <div class="deco-wave"></div>
    </div>

    <script>
        // Forzar token en mayúscula al escribir
        document.getElementById('token').addEventListener('input', function () {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>
