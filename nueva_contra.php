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

            // ✅ Corrección aquí: el nombre de la columna es 'password'
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
        $mensaje = '<div class="alert alert-danger">' . implode('<br>', $errores) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Sanacita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="nueva_contra.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 500px;">
            <div class="card-header text-center">
                <h4>Nueva Contraseña</h4>
                <small>Sistema Médico Sanacita</small>
            </div>
            <div class="card-body">
                <form method="POST" id="passwordForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <?php if (!empty($mensaje)) echo $mensaje; ?>

                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva contraseña</label>
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               id="password"
                               minlength="8"
                               required
                               autocomplete="new-password"
                               oninput="checkPasswordStrength(this.value)">
                        <div class="progress mt-2">
                            <div class="progress-bar" id="strength-fill" style="width: 0%;"></div>
                        </div>
                        <small class="text-muted">Debe tener al menos 8 caracteres con mayúsculas, minúsculas y números</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar contraseña</label>
                        <input type="password" 
                               class="form-control" 
                               name="confirm_password" 
                               id="confirm_password"
                               minlength="8"
                               required
                               autocomplete="new-password"
                               oninput="validatePasswordMatch()">
                        <div id="confirm-help" class="form-text"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Actualizar Contraseña</button>
                </form>
            </div>
        </div>
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
            fill.classList.add(
                strength < 3 ? 'bg-danger' :
                strength < 5 ? 'bg-warning' : 'bg-success'
            );
        }

        function validatePasswordMatch() {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const help = document.getElementById('confirm-help');

            if (pass && confirm) {
                help.textContent = pass === confirm ? 'Las contraseñas coinciden' : 'Las contraseñas no coinciden';
                help.className = pass === confirm ? 'form-text text-success' : 'form-text text-danger';
            } else {
                help.textContent = '';
            }
        }

        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            if (pass !== confirm) {
                e.preventDefault();
                document.getElementById('confirm-help').textContent = 'Por favor, asegúrese que las contraseñas coincidan';
                document.getElementById('confirm-help').className = 'form-text text-danger';
            }
        });
    </script>
</body>
</html>
