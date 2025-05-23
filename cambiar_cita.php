<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_cita = $_GET['id_cita'] ?? null;

// 1. Verificar que la cita le pertenezca
$stmt = $conn->prepare("SELECT id_centro, fecha FROM cita WHERE id_cita = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_cita, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Cita no encontrada.";
    exit;
}

$cita = $result->fetch_assoc();
$id_centro = $cita['id_centro'];
$fecha = $cita['fecha'];

// 2. Obtener horarios disponibles para ese día en ese centro
$horarios_disponibles = [];
$horas = ['09:00', '10:00', '11:00', '12:00', '13:00', '15:00', '16:00', '17:00'];

$sql = "SELECT horario FROM cita WHERE fecha = ? AND id_centro = ? AND estado = 'pendiente'";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("si", $fecha, $id_centro);
$stmt2->execute();
$res = $stmt2->get_result();
$ocupadas = [];

while ($row = $res->fetch_assoc()) {
    $ocupadas[] = $row['horario'];
}

// 3. Mostrar formulario
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reprogramar Cita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Reprogramar Cita</h2>
    <p>Selecciona un nuevo horario disponible para el día <strong><?= $fecha ?></strong>.</p>

    <form action="guardar_cambio_cita.php" method="POST">
        <input type="hidden" name="id_cita" value="<?= $id_cita ?>">

        <div class="mb-3">
            <label for="nuevo_horario" class="form-label">Horario disponible:</label>
            <select name="nuevo_horario" id="nuevo_horario" class="form-select" required>
                <?php foreach ($horas as $hora): ?>
                    <?php if (!in_array($hora, $ocupadas)): ?>
                        <option value="<?= $hora ?>"><?= $hora ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Guardar cambios</button>
        <a href="citas.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>
