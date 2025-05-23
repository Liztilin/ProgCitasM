<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT id_notificacion, titulo, mensaje, fecha, leido 
        FROM notificaciones 
        WHERE id_usuario = ? 
        ORDER BY fecha DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Notificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Notificaciones</h2>

    <?php if ($result->num_rows > 0): ?>
        <ul class="list-group">
            <?php while ($row = $result->fetch_assoc()): ?>
                <li class="list-group-item <?= $row['leido'] ? 'text-muted' : '' ?>">
                    <strong><?= htmlspecialchars($row['titulo']) ?></strong><br>
                    <?= htmlspecialchars($row['mensaje']) ?><br>
                    <small class="text-muted"><?= $row['fecha'] ?></small>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-info">No tienes notificaciones.</div>
        <span class="badge bg-success">Oportunidad</span> <!-- Si tipo = oportunidad -->

    <?php endif; ?>
</div>
</body>
</html>
