<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_medico'])) {
    header("Location: login_medico.php");
    exit;
}

$id_usuario = $_GET['id_usuario'] ?? null;

if (!$id_usuario) {
    echo "ID de usuario no proporcionado.";
    exit;
}

// Obtener datos del paciente
$sql_paciente = "SELECT nombre, apellido_p, apellido_m FROM usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_paciente);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res_paciente = $stmt->get_result();
$paciente = $res_paciente->fetch_assoc();
$stmt->close();

// Obtener citas con diagnóstico
$sql = "SELECT c.fecha, c.horario, cs.nombre_centro, d.diagnostico, d.prescripcion, d.fecha_registro
        FROM cita c
        JOIN centro_salud cs ON c.id_centro = cs.id_centro
        JOIN diagnostico d ON d.id_cita = c.id_cita
        WHERE c.id_usuario = ?
        ORDER BY c.fecha DESC, c.horario ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Clínico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3>Historial clínico de <?= $paciente['nombre'] . " " . $paciente['apellido_p'] . " " . $paciente['apellido_m'] ?></h3>

    <?php if ($resultado->num_rows > 0): ?>
        <table class="table table-bordered mt-4">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Centro de Salud</th>
                    <th>Diagnóstico</th>
                    <th>Prescripción</th>
                    <th>Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['fecha']) ?></td>
                        <td><?= htmlspecialchars($fila['horario']) ?></td>
                        <td><?= htmlspecialchars($fila['nombre_centro']) ?></td>
                        <td><?= nl2br(htmlspecialchars($fila['diagnostico'])) ?></td>
                        <td><?= nl2br(htmlspecialchars($fila['prescripcion'])) ?></td>
                        <td><?= htmlspecialchars($fila['fecha_registro']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info mt-4">Este paciente aún no tiene diagnósticos registrados.</div>
    <?php endif; ?>

    <a href="citas_medico.php" class="btn btn-secondary mt-3">Volver</a>
</div>

</body>
</html>
