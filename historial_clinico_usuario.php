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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Clínico - Sanacita</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --light-blue: #e3f2fd;
            --success-color: #28a745;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        
        .table thead {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .table th {
            border: none;
            padding: 15px;
            font-weight: 500;
        }
        
        .table td {
            vertical-align: middle;
            padding: 15px;
        }
        
        .medical-record {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .record-header {
            background-color: var(--light-blue);
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .record-body {
            padding: 15px;
        }
        
        .record-title {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .record-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .record-content {
            margin-top: 15px;
        }
        
        .content-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .content-text {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            white-space: pre-line;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .welcome-text {
            font-weight: 500;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #adb5bd;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: #6c757d;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="Imagenes progsanacita/logo_sanacita2.png" alt="Logo" height="40" class="me-2">
                    <h4 class="mb-0">Sanacita</h4>
                </div>
                <div class="d-flex align-items-center">
                    <span class="welcome-text me-2">Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['usuario_nombre'] ?? 'U') ?>&background=random" alt="User" class="user-avatar">
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title mb-0"><i class="bi bi-file-medical me-2"></i>Historial Clínico</h2>
                <p class="text-muted mb-0">Registro completo de tus diagnósticos y tratamientos</p>
            </div>
        </div>

        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <div class="medical-record">
                    <div class="record-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="record-title">Consulta médica</h5>
                                <div class="record-meta">
                                    <span><i class="bi bi-calendar-date me-1"></i><?= date('d/m/Y', strtotime($row['fecha'])) ?></span>
                                    <span class="mx-2">•</span>
                                    <span><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($row['horario'])) ?></span>
                                    <span class="mx-2">•</span>
                                    <span><i class="bi bi-hospital me-1"></i><?= htmlspecialchars($row['nombre_centro']) ?></span>
                                </div>
                            </div>
                            <div class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Completada
                            </div>
                        </div>
                    </div>
                    <div class="record-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="record-content">
                                    <h6 class="content-label"><i class="bi bi-person-badge me-1"></i>Médico tratante</h6>
                                    <p><?= htmlspecialchars($row['nombre_medico'] . ' ' . $row['apellido_p'] . ' ' . $row['apellido_m']) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="record-content">
                                    <h6 class="content-label"><i class="bi bi-clipboard2-pulse me-1"></i>Diagnóstico</h6>
                                    <div class="content-text"><?= nl2br(htmlspecialchars($row['diagnostico'])) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="record-content">
                                    <h6 class="content-label"><i class="bi bi-capsule me-1"></i>Prescripción médica</h6>
                                    <div class="content-text"><?= nl2br(htmlspecialchars($row['prescripcion'])) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card">
                <div class="empty-state">
                    <i class="bi bi-file-earmark-medical"></i>
                    <h4>No hay registros médicos</h4>
                    <p>Aún no tienes historial clínico disponible. Tus consultas médicas aparecerán aquí una vez completadas.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>