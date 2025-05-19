<?php
session_start();
require 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $mensaje = '<div class="error">Todos los campos son obligatorios</div>';
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

                header("Location: Iniciovr2.html");
                exit();
            } else {
                $mensaje = '<div class="error">Credenciales incorrectas</div>';
            }
        } else {
            $mensaje = '<div class="error">Credenciales incorrectas</div>';
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
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .error {
            color: white;
            background-color: #e74c3c;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>¡Bienvenido!</h1>

        <?php if (!empty($mensaje)) echo $mensaje; ?>

        <form class="login-form" method="POST" action="">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com">

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required placeholder="********">

            <div class="options">
                <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit">Entrar</button>
        </form>

        <p class="register-link">¿No tienes una cuenta? <a href="crear_cuenta.php">Crear una cuenta</a></p>
    </div>
</body>
</html>
