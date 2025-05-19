<?php
session_start();

if (!isset($_SESSION['id_medico'])) {
    header("Location: login_medico.php");
    exit;
}

require_once 'conexion.php';

$id_medico = $_SESSION['id_medico'];

// Obtener el centro de salud del médico
$sql_centro = "SELECT id_centro FROM medico WHERE id_medico = ?";
$stmt_centro = $conn->prepare($sql_centro);
$stmt_centro->bind_param("i", $id_medico);
$stmt_centro->execute();
$result = $stmt_centro->get_result();
$centro_data = $result->fetch_assoc();
$id_centro = $centro_data['id_centro'];
$stmt_centro->close();

// Determinar la fecha a consultar (hoy o la que el médico seleccione)
$fecha_consulta = date('Y-m-d');
if (isset($_GET['fecha'])) {
    $fecha_consulta = $_GET['fecha'];
}

// Consultar las citas del día
$sql = "SELECT c.id_cita, c.id_usuario, u.nombre, u.apellido_p, u.apellido_m, c.fecha, c.horario, c.estado
        FROM cita c
        JOIN usuario u ON c.id_usuario = u.id_usuario
        WHERE c.id_centro = ? AND c.fecha = ?
        ORDER BY c.horario ASC";


$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_centro, $fecha_consulta);
$stmt->execute();
$citas = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Citas del Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">Citas del <?= $fecha_consulta ?></h2>

    <!-- Cambiar fecha de consulta -->
    <form method="GET" class="mb-3">
        <label for="fecha">Seleccionar fecha:</label>
        <input type="date" name="fecha" id="fecha" class="form-control" value="<?= $fecha_consulta ?>" onchange="this.form.submit()">
    </form>

    <?php if ($citas->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>Nombre del paciente</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                    <th>Diagnóstico</th>
                    <th>Historial</th>

                </tr>
            </thead>
            <tbody>
                <?php while ($cita = $citas->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($cita['nombre'] . ' ' . $cita['apellido_p'] . ' ' . $cita['apellido_m']) ?></td>
                        <td><?= htmlspecialchars($cita['fecha']) ?></td>
                        <td><?= htmlspecialchars($cita['horario']) ?></td>
                        <td><?= htmlspecialchars($cita['estado']) ?></td>
                        <td><a href="registrar_diagnostico.php?id_cita=<?= $cita['id_cita'] ?>" class="btn btn-sm btn-success">Registrar</a></td>
                        <td><a href="historial_clinico.php?id_usuario=<?= $cita['id_usuario'] ?>" class="btn btn-sm btn-info">Ver</a></td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No hay citas para esta fecha.</div>
    <?php endif; ?>
</div>

</body>
</html>
