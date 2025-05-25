<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_medico'])) {
    header("Location: login_medico.php");
    exit;
}

$id_usuario = $_GET['id_usuario'] ?? null;

if (!$id_usuario) {
    echo "<div class='alert alert-danger'>ID de usuario no proporcionado.</div>";
    exit;
}

// Obtener datos básicos del paciente
$sql_paciente = "SELECT nombre, apellido_p, apellido_m FROM usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_paciente);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res_paciente = $stmt->get_result();
$paciente = $res_paciente->fetch_assoc();
$stmt->close();

// Obtener citas con diagnóstico e información del médico
$sql = "SELECT c.fecha, c.horario, cs.nombre_centro, d.diagnostico, d.prescripcion, 
               m.nombre as medico_nombre, m.apellido_p as medico_apellido_p, m.apellido_m as medico_apellido_m
        FROM cita c
        JOIN centro_salud cs ON c.id_centro = cs.id_centro
        JOIN diagnostico d ON d.id_cita = c.id_cita
        JOIN medico m ON d.id_medico = m.id_medico
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
        
        .patient-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
        }
        
        .patient-header {
            background-color: var(--light-blue);
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
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
        
        .content-text {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            white-space: pre-line;
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
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="imagenes progsanacita/logo_sanacita2.png" alt="Logo" height="40" class="me-2">
                    <h4 class="mb-0">Sanacita - Historial Clínico</h4>
                </div>
                <a href="citas_medico.php" class="btn btn-light">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Tarjeta de información del paciente -->
        <div class="card patient-card">
            <div class="patient-header">
                <h4><i class="bi bi-person me-2"></i>Paciente: <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_p'] . ' ' . $paciente['apellido_m']) ?></h4>
            </div>
        </div>

        <!-- Historial médico -->
        <h3 class="mb-4"><i class="bi bi-file-medical me-2"></i>Registros Médicos</h3>

        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <div class="medical-record">
                    <div class="record-header">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div>
                                <h5 class="record-title">Consulta médica</h5>
                                <div class="record-meta">
                                    <span><i class="bi bi-calendar-date me-1"></i><?= date('d/m/Y', strtotime($fila['fecha'])) ?></span>
                                    <span class="mx-2">•</span>
                                    <span><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($fila['horario'])) ?></span>
                                </div>
                            </div>
                            <div class="record-meta">
                                <i class="bi bi-hospital me-1"></i><?= htmlspecialchars($fila['nombre_centro']) ?>
                            </div>
                            <div class="record-meta">
                                <i class="bi bi-person-badge me-1"></i>
                                Dr. <?= htmlspecialchars($fila['medico_nombre'] . ' ' . $fila['medico_apellido_p'] . ' ' . $fila['medico_apellido_m']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="record-body">
                        <div class="mb-3">
                            <h6><i class="bi bi-clipboard2-pulse me-1"></i>Diagnóstico</h6>
                            <div class="content-text"><?= nl2br(htmlspecialchars($fila['diagnostico'])) ?></div>
                        </div>
                        <div class="mb-3">
                            <h6><i class="bi bi-capsule me-1"></i>Prescripción médica</h6>
                            <div class="content-text"><?= nl2br(htmlspecialchars($fila['prescripcion'])) ?></div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card">
                <div class="empty-state">
                    <i class="bi bi-file-earmark-medical"></i>
                    <h4>No hay registros médicos</h4>
                    <p>Este paciente no tiene historial clínico registrado.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>