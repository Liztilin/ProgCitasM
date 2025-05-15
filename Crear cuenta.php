<?php
require 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $apellidos = filter_input(INPUT_POST, 'apellidos', FILTER_SANITIZE_STRING);
    $edad = filter_input(INPUT_POST, 'edad', FILTER_SANITIZE_NUMBER_INT);
    $genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $terminos = isset($_POST['terminos']) ? 1 : 0;

    // Validaciones
    if (!$nombre || !$apellidos || !$edad || !$genero || !$email || !$password) {
        $mensaje = '<div class="error">Todos los campos son obligatorios</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = '<div class="error">El correo electrónico no es válido</div>';
    } elseif (!$terminos) {
        $mensaje = '<div class="error">Debes aceptar los términos y condiciones</div>';
    } else {
        // Intentar registrar el usuario
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, edad, genero, email, password, acepto_terminos) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssi", $nombre, $apellidos, $edad, $genero, $email, $password, $terminos);

        if ($stmt->execute()) {
            $mensaje = '<div class="exito">¡Registro exitoso! Bienvenido a nuestro sistema de citas médicas.</div>';
            echo "<script>window.location.href = 'login.php';</script>";
        } elseif ($stmt->errno == 1062) { // Error: email ya existe
            $mensaje = '<div class="error">El correo electrónico ya está registrado</div>';
        } else {
            $mensaje = '<div class="error">Error al registrar: ' . $stmt->error . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="Crear cuenta.css">
</head>
<body>
    <div class="form-container">
        <div class="circle-decoration"></div>
        <div class="form-content">
            <h1 class="form-title">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Crear Cuenta Nueva
            </h1>
            
            <form id="registrationForm" novalidate>
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" required pattern="[A-Za-zÀ-ÿ\s]+" minlength="2" oninput="validateField(this)">
                    <p id="nombreError" class="error-message">El nombre debe tener al menos 2 caracteres y solo letras</p>
                </div>

                <div class="form-group">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" required pattern="[A-Za-zÀ-ÿ\s]+" minlength="2" oninput="validateField(this)">
                    <p id="apellidosError" class="error-message">Los apellidos deben tener al menos 2 caracteres y solo letras</p>
                </div>

                <div class="form-group">
                    <label for="edad">Edad</label>
                    <input type="range" id="edad" name="edad" min="1" max="150" value="25" oninput="document.getElementById('edadValue').textContent = this.value">
                    <span id="edadValue" class="edad-display">25</span>
                </div>

                <div class="form-group">
                    <label>Género</label>
                    <label><input type="radio" name="genero" value="masculino" required> Masculino</label>
                    <label><input type="radio" name="genero" value="femenino" required> Femenino</label>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required oninput="validateField(this)">
                    <p id="emailError" class="error-message">Ingrese un correo electrónico válido</p>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required minlength="8" oninput="validateField(this)">
                    <p id="passwordError" class="error-message">La contraseña debe tener al menos 8 caracteres</p>
                </div>

                <button type="submit" id="submitBtn" disabled>Crear Cuenta</button>
            </form>
        </div>
    </div>

    <script>
        function validateField(input) {
            const errorElement = document.getElementById(`${input.id}Error`);
            const submitButton = document.getElementById('submitBtn');
            let isValid = true;

            if (input.type === 'email') {
                isValid = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(input.value);
            } else if (input.type === 'password') {
                isValid = input.value.length >= 8;
            } else if (input.type === 'text') {
                isValid = input.value.length >= 2 && /^[A-Za-zÀ-ÿ\\s]+$/.test(input.value);
            }

            if (!isValid) {
                input.classList.add('input-error');
                errorElement?.classList.add('visible');
            } else {
                input.classList.remove('input-error');
                errorElement?.classList.remove('visible');
            }

            const form = document.getElementById('registrationForm');
            const allInputs = form.querySelectorAll('input:not([type="range"])');
            const allValid = Array.from(allInputs).every(input => {
                if (input.type === 'radio') {
                    return document.querySelector('input[name="genero"]:checked');
                }
                return input.checkValidity() && input.value.length > 0;
            });

            submitButton.disabled = !allValid;
        }

        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (this.checkValidity()) {
                alert('Formulario enviado correctamente');
            }
        });
    </script>
</body>
</html>
