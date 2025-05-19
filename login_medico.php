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
    <title>Login Médico - Sanacita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 450px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-4 text-center">Ingreso Médico</h3>

            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger"><?= $mensaje_error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="clave" class="form-label">Clave de acceso</label>
                    <input type="text" name="clave" id="clave" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
