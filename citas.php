<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}
$id_usuario = $_SESSION['id_usuario'];


require_once 'conexion.php';


// Consultar citas del usuario
$sql = "SELECT c.fecha, c.horario, cs.nombre_centro, c.estado
        FROM cita c
        JOIN centro_salud cs ON c.id_centro = cs.id_centro
        WHERE c.id_usuario = ?
        ORDER BY c.fecha DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

?>
<?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
    <div class="alert alert-danger">El horario seleccionado ya está ocupado. Elige otro.</div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Citas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">Mis Citas</h2>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">¡Cita agendada con éxito!</div>
    <?php endif; ?>

    <!-- Botón para sacar nueva cita -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalNuevaCita">
        Sacar nueva cita
    </button>

    <!-- Tabla de citas -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Centro de Salud</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['fecha']) ?></td>
                    <td><?= htmlspecialchars($fila['horario']) ?></td>
                    <td><?= htmlspecialchars($fila['nombre_centro']) ?></td>
                    <td><?= htmlspecialchars($fila['estado']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal de nueva cita -->
<?php include 'modal_cita.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
