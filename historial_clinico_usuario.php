<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Consulta de historial clínico
$sql = "
    SELECT 
        c.fecha, c.horario, cs.nombre_centro, 
        d.diagnostico, d.prescripcion, 
        m.nombre AS nombre_medico, m.apellido_p, m.apellido_m
    FROM cita c
    JOIN diagnostico d ON c.id_cita = d.id_cita
    JOIN medico m ON d.id_medico = m.id_medico
    JOIN centro_salud cs ON c.id_centro = cs.id_centro
    WHERE c.id_usuario = ? AND c.estado = 'completada'
    ORDER BY c.fecha DESC, c.horario DESC
";

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
        <h2 class="mb-4">Historial Clínico</h2>

        <?php if ($resultado->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Centro de Salud</th>
                            <th>Médico</th>
                            <th>Diagnóstico</th>
                            <th>Prescripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['fecha']) ?></td>
                                <td><?= htmlspecialchars($row['horario']) ?></td>
                                <td><?= htmlspecialchars($row['nombre_centro']) ?></td>
                                <td><?= htmlspecialchars($row['nombre_medico'] . ' ' . $row['apellido_p'] . ' ' . $row['apellido_m']) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['diagnostico'])) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['prescripcion'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Aún no tienes historial clínico disponible.</div>
        <?php endif; ?>
    </div>
</body>
</html>
