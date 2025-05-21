<?php
session_start();
require 'conexion.php';

// Mostrar errores (desactiva en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensaje = '';

// Validar token de sesión de recuperación
if (!isset($_SESSION['token_valido']) || !$_SESSION['token_valido']) {
    header('Location: recuperacion.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token CSRF inválido');
    }

    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_SESSION['email_recuperacion'] ?? '';
    $id_usuario = $_SESSION['id_usuario_recuperacion'] ?? null;

    $errores = [];

    if (empty($password) || empty($confirm_password)) {
        $errores[] = 'Ambos campos son requeridos';
    } elseif ($password !== $confirm_password) {
        $errores[] = 'Las contraseñas no coinciden. Por favor, inténtelo de nuevo.';
    } elseif (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres';
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
        $errores[] = 'Debe contener mayúsculas, minúsculas y números';
    }

    if (empty($errores)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $conn->begin_transaction();

            $stmt = $conn->prepare("UPDATE usuario SET password = ? WHERE id_usuario = ?");
            $stmt->bind_param("si", $hashed_password, $id_usuario);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("No se pudo actualizar la contraseña. Usuario no encontrado.");
            }

            $stmt = $conn->prepare("DELETE FROM token_recuperacion WHERE id_usuario = ?");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO logs_seguridad (id_usuario, accion, fecha) 
                                   VALUES (?, 'Cambio de contraseña', NOW())");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();

            $conn->commit();

            unset($_SESSION['email_recuperacion'], $_SESSION['token_valido'], $_SESSION['csrf_token'], $_SESSION['id_usuario_recuperacion']);
            unset($password, $confirm_password, $hashed_password);

            $_SESSION['exito'] = 'Contraseña actualizada exitosamente';
            header('Location: login.php');
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error actualizando contraseña: " . $e->getMessage());
            $errores[] = 'Error en el sistema. Por favor, inténtelo más tarde.';
        }
    }

    if (!empty($errores)) {
        $mensaje = '<div class="alert error">' . implode('<br>', $errores) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Sanacita</title>
    <link rel="stylesheet" href="nueva_contra.css">
</head>
<body>
    <div class="medical-wrapper">
        <div class="medical-card">
            <div class="medical-header">
                <div class="medical-logo">
                    <img src="imagenes progsanacita/logo_sanacita2.png" alt="Sanacita Logo">
                </div>
                <h1 class="medical-title">
                    <span>Nueva Contraseña</span>
                    <small>Sistema Médico Sanacita</small>
                </h1>
            </div>

            <?php if (!empty($mensaje)) echo $mensaje; ?>

            <form method="POST" id="passwordForm" class="medical-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="input-container">
                    <input type="password" 
                           name="password" 
                           id="password"
                           placeholder="Nueva contraseña"
                           minlength="8"
                           required
                           autocomplete="new-password"
                           oninput="checkPasswordStrength(this.value)">
                    <svg class="input-icon" viewBox="0 0 24 24">
                        <path d="M12 1C8.96243 1 6.5 3.46243 6.5 6.5V9H5V21H19V9H17.5V6.5C17.5 3.46243 15.0376 1 12 1ZM12 3C14.1365 3 15.9 4.76354 15.9 6.9V9H8.1V6.9C8.1 4.76354 9.86346 3 12 3ZM12 11C12.5523 11 13 11.4477 13 12C13 12.5523 12.5523 13 12 13C11.4477 13 11 12.5523 11 12C11 11.4477 11.4477 11 12 11ZM9 12C9 10.3431 10.3431 9 12 9C13.6569 9 15 10.3431 15 12C15 13.6569 13.6569 15 12 15C10.3431 15 9 13.6569 9 12Z"/>
                    </svg>
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar" id="strength-fill"></div>
                        </div>
                        <div class="password-hint">Debe contener mayúsculas, minúsculas y números</div>
                    </div>
                </div>

                <div class="input-container">
                    <input type="password" 
                           name="confirm_password" 
                           id="confirm_password"
                           placeholder="Confirmar contraseña"
                           minlength="8"
                           required
                           autocomplete="new-password"
                           oninput="validatePasswordMatch()">
                    <svg class="input-icon" viewBox="0 0 24 24">
                        <path d="M17.5 9V6.5C17.5 3.46243 15.0376 1 12 1C8.96243 1 6.5 3.46243 6.5 6.5V9H5V21H19V9H17.5ZM8.1 6.9C8.1 4.76354 9.86346 3 12 3C14.1365 3 15.9 4.76354 15.9 6.9V9H8.1V6.9ZM12 15C10.3431 15 9 13.6569 9 12C9 10.3431 10.3431 9 12 9C13.6569 9 15 10.3431 15 12C15 13.6569 13.6569 15 12 15Z"/>
                    </svg>
                    <div id="confirm-help" class="match-text"></div>
                </div>

                <button type="submit" class="medical-btn">
                    Actualizar Contraseña
                    <svg class="btn-icon" viewBox="0 0 24 24">
                        <path d="M5 13h11.17l-4.88 4.88c-.39.39-.39 1.03 0 1.42.39.39 1.02.39 1.41 0l6.59-6.59c.39-.39.39-1.02 0-1.41l-6.58-6.6c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L16.17 11H5c-.55 0-1 .45-1 1s.45 1 1 1z"/>
                    </svg>
                </button>
            </form>

            <div class="medical-footer">
                <div class="divider"></div>
                <p class="login-link">¿Recordó su contraseña? <a href="login.php">Ingresar ahora</a></p>
            </div>
        </div>
        
        <!-- Elementos decorativos de fondo -->
        <div class="deco-circle deco-1"></div>
        <div class="deco-circle deco-2"></div>
        <div class="deco-wave"></div>
    </div>

    <script>
        function checkPasswordStrength(password) {
            const fill = document.getElementById('strength-fill');
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            const width = (strength / 5) * 100;
            fill.style.width = width + '%';
            fill.className = 'progress-bar';
            
            if (strength < 2) {
                fill.style.backgroundColor = '#e74c3c';
            } else if (strength < 4) {
                fill.style.backgroundColor = '#f39c12';
            } else {
                fill.style.backgroundColor = '#27ae60';
            }
        }

        function validatePasswordMatch() {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const help = document.getElementById('confirm-help');

            if (pass && confirm) {
                help.textContent = pass === confirm ? '✓ Las contraseñas coinciden' : '✗ Las contraseñas no coinciden';
                help.className = pass === confirm ? 'match-text success' : 'match-text error';
            } else {
                help.textContent = '';
            }
        }

        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            if (pass !== confirm) {
                e.preventDefault();
                document.getElementById('confirm-help').textContent = '✗ Por favor, asegúrese que las contraseñas coincidan';
                document.getElementById('confirm-help').className = 'match-text error';
            }
        });
    </script>
</body>
</html>