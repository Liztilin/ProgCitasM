<?php
require 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos
    $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
    $apellido_p = trim(filter_input(INPUT_POST, 'apellido_p', FILTER_SANITIZE_STRING));
    $apellido_m = trim(filter_input(INPUT_POST, 'apellido_m', FILTER_SANITIZE_STRING));
    $edad = filter_input(INPUT_POST, 'edad', FILTER_SANITIZE_NUMBER_INT);
    $genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validaciones
    if (!$nombre || !$apellido_p || !$apellido_m || !$edad || !$genero || !$email || !$password) {
        $mensaje = '<div class="error">Todos los campos son obligatorios</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = '<div class="error">El correo electrónico no es válido</div>';
    } else {
        // Verificar si el correo ya existe
        $check_email = $conn->prepare("SELECT email FROM usuario WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();
        
        if ($check_email->num_rows > 0) {
            $mensaje = '<div class="error">El correo electrónico ya está registrado</div>';
        } else {
            $stmt = $conn->prepare("INSERT INTO usuario (nombre, apellido_p, apellido_m, edad, genero, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssisss", $nombre, $apellido_p, $apellido_m, $edad, $genero, $email, $password);

            if ($stmt->execute()) {
                $mensaje = '<div class="exito">¡Registro exitoso! Bienvenido a nuestro sistema de citas médicas.</div>';
                echo "<script>window.location.href = 'login.php';</script>";
            } else {
                $mensaje = '<div class="error">Error al registrar: ' . $stmt->error . '</div>';
            }
        }
        $check_email->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registro | Sistema Médico Azul</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="Crear cuenta.css" />
</head>
<body>
    <div class="form-container">
        <div class="form-content animate__animated animate__fadeIn">
            <div class="logo">
                <img src="imagenes progsanacita/logo_sanacita2.png" alt="Logo Sanacita" class="logo-img" />
            </div>
            <h1 class="form-title">Registro de Paciente</h1>
            <p class="form-subtitle">Complete sus datos para acceder a nuestro sistema de citas médicas</p>

            <?php if (!empty($mensaje)): ?>
                <div class="alert <?php echo strpos($mensaje, 'error') !== false ? 'alert-error' : 'alert-success'; ?>">
                    <?php echo strip_tags($mensaje, '<div>'); ?>
                </div>
            <?php endif; ?>

            <form id="registrationForm" method="POST" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input
                                type="text"
                                class="form-control"
                                id="nombre"
                                name="nombre"
                                required
                                pattern="[A-Za-zÀ-ÿ\s]+"
                                minlength="2"
                                oninput="validateField(this)"
                            />
                            <p id="nombreError" class="error-message">El nombre debe tener al menos 2 caracteres y solo letras</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="apellido_p">Apellido Paterno</label>
                            <input
                                type="text"
                                class="form-control"
                                id="apellido_p"
                                name="apellido_p"
                                required
                                pattern="[A-Za-zÀ-ÿ\s]+"
                                minlength="2"
                                oninput="validateField(this)"
                            />
                            <p id="apellido_pError" class="error-message">Debe tener al menos 2 caracteres y solo letras</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="apellido_m">Apellido Materno</label>
                            <input
                                type="text"
                                class="form-control"
                                id="apellido_m"
                                name="apellido_m"
                                required
                                pattern="[A-Za-zÀ-ÿ\s]+"
                                minlength="2"
                                oninput="validateField(this)"
                            />
                            <p id="apellido_mError" class="error-message">Debe tener al menos 2 caracteres y solo letras</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Género</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" id="masculino" name="genero" value="masculino" required />
                                    <label for="masculino">Masculino</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="femenino" name="genero" value="femenino" required />
                                    <label for="femenino">Femenino</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edad">Edad: <span id="edadValue" class="edad-display">25</span></label>
                    <input
                        type="range"
                        class="form-range"
                        id="edad"
                        name="edad"
                        min="1"
                        max="150"
                        value="25"
                        oninput="document.getElementById('edadValue').textContent = this.value"
                    />
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        required
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
                        minlength="8"
                        oninput="validateField(this)"
                    />
                    <p id="passwordError" class="error-message">La contraseña debe tener al menos 8 caracteres</p>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terminos" name="terminos" required />
                        <label class="form-check-label" for="terminos"
                            >Acepto los
                            <a href="#" style="color: var(--primary-color)">términos y condiciones</a></label
                        >
                    </div>
                </div>

                <button type="submit" class="btn-primary" id="submitBtn" disabled>Registrarse</button>

                <div class="login-link">
                    ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateField(input) {
            const errorElement = document.getElementById(`${input.id}Error`);
            const submitButton = document.getElementById('submitBtn');
            let isValid = true;

            if (input.type === 'email') {
                isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
            } else if (input.type === 'password') {
                isValid = input.value.length >= 8;
            } else if (input.type === 'text') {
                isValid = /^[A-Za-zÀ-ÿ]+(?: [A-Za-zÀ-ÿ]+)*$/.test(input.value.trim());
            }

            if (!isValid) {
                input.classList.add('is-invalid');
                errorElement?.classList.add('visible');
            } else {
                input.classList.remove('is-invalid');
                errorElement?.classList.remove('visible');
            }

            const form = document.getElementById('registrationForm');
            const allInputs = form.querySelectorAll('input:not([type="range"])');
            const allValid = Array.from(allInputs).every((input) => {
                if (input.type === 'radio') {
                    return document.querySelector('input[name="genero"]:checked');
                }
                if (input.type === 'checkbox') {
                    return input.checked;
                }
                return input.checkValidity() && input.value.trim().length > 0;
            });

            submitButton.disabled = !allValid;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('#registrationForm input');
            inputs.forEach((input) => {
                if (input.value) validateField(input);
                input.addEventListener('blur', () => validateField(input));
            });
            
            document.querySelector('button').addEventListener('click', () => {
                document.documentElement.style.setProperty('--background-color', '#ffebee')};
});
        });
    </script>
</body>
</html> 