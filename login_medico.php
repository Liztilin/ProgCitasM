<?php
session_start();

require_once 'conexion.php';

$mensaje_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clave = $_POST['clave'];
    $password = $_POST['password'];

    $sql = "SELECT id_medico, password FROM medico WHERE clave_acceso = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $clave);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $medico = $resultado->fetch_assoc();

        if (password_verify($password, $medico['password'])) {
            $_SESSION['id_medico'] = $medico['id_medico'];
            header("Location: citas_medico.php");
            exit;
        } else {
            $mensaje_error = "Contraseña incorrecta.";
        }
    } else {
        $mensaje_error = "Clave de acceso no encontrada.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Médico - Sanacita</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color:rgb(117, 181, 224);
            --secondary-color:rgb(31, 105, 179);
            --light-blue: #e3f2fd;
            --white: #ffffff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background-color:#e3f2fd;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-card {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: var(--white);
            padding: 1.5rem;
            text-align: center;
            border-bottom: none;
        }
        
        .card-title {
            font-weight: 600;
            margin-bottom: 0;
            font-size: 1.5rem;
        }
        
        .card-body {
            padding: 2rem;
            background-color:rgb(255, 255, 255);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .input-group-text {
            background-color: var(--light-blue);
            border-color: #ced4da;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .alert-danger {
            border-left: 4px solid #dc3545;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .medical-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card-header">
                <i class="bi bi-heart-pulse medical-icon"></i>
                <h3 class="card-title">Acceso Médico</h3>
            </div>
            <div class="card-body">
                <?php if ($mensaje_error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= $mensaje_error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label for="clave" class="form-label">Clave de Acceso</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                            <input type="text" name="clave" id="clave" class="form-control" placeholder="Ingresa tu clave de acceso" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Ingresa tu contraseña" required>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>