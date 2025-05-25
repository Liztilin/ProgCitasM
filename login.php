<?php
session_start();
require 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $mensaje = '<div class="alert alert-error">Todos los campos son obligatorios</div>';
    } else {
        $stmt = $conn->prepare("SELECT id_usuario, nombre, password FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();

            if (password_verify($password, $usuario['password'])) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $email;
                $_SESSION['email_recuperacion'];
                $_SESSION['id_usuario_recuperacion'];


                header("Location: Iniciovr2.php");
                exit();
            } else {
                $mensaje = '<div class="alert alert-error">Credenciales incorrectas</div>';
            }
        } else {
            $mensaje = '<div class="alert alert-error">Credenciales incorrectas</div>';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Iniciar Sesión | Sistema Médico Azul</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="login.css" />
</head>
<body>
    <div class="form-container">
        <div class="form-content animate__animated animate__fadeIn">
            <div class="logo">
                <img src="Imagenes progsanacita/logo_sanacita2.png" alt="Logo Sanacita" class="logo-img" />
            </div>
            <h1 class="form-title">Iniciar Sesión</h1>
            <p class="form-subtitle">Ingrese sus credenciales para acceder al sistema</p>

            <?php if (!empty($mensaje)) echo $mensaje; ?>
            <form class="login-form" method="POST" action="">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        required
                        placeholder="correo@ejemplo.com"
                        oninput="validateField(this)"
                    />
                    <p id="emailError" class="error-message">Ingrese un correo electrónico válido</p>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        required
                        placeholder="********"
                        minlength="8"
                        oninput="validateField(this)"
                    />
                    <p id="passwordError" class="error-message">La contraseña debe tener al menos 8 caracteres</p>
                </div>

                <div class="options">
                    <a href="olvidaste_contraseña.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-primary">Entrar</button>
            </form>

            <div class="login-link">
                ¿No tienes una cuenta? <a href="Crear cuenta.php" style="color: var(--primary-color)">Crear una cuenta</a>
            </div>
        </div>
    </div>

    <script>
        function validateField(input) {
            const errorElement = document.getElementById(`${input.id}Error`);
            let isValid = true;

            if (input.type === 'email') {
                isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
            } else if (input.type === 'password') {
                isValid = input.value.length >= 8;
            }

            if (!isValid) {
                input.classList.add('is-invalid');
                errorElement?.classList.add('visible');
            } else {
                input.classList.remove('is-invalid');
                errorElement?.classList.remove('visible');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('.login-form input');
            inputs.forEach((input) => {
                if (input.value) validateField(input);
                input.addEventListener('blur', () => validateField(input));
            });
        });
    </script>
</body>
</html>