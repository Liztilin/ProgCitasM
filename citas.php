<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}
$id_usuario = $_SESSION['id_usuario'];

require_once 'conexion.php';

// Consultar citas del usuario
$sql = "SELECT c.id_cita,c.fecha, c.horario, cs.nombre_centro, c.estado
        FROM cita c
        JOIN centro_salud cs ON c.id_centro = cs.id_centro
        WHERE c.id_usuario = ?
        ORDER BY c.fecha DESC";

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
    <title>Mis Citas - Sanacita</title>
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
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 10px 20px;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pendiente {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-confirmada {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelada {
            background-color: #f8d7da;
            color: #721c24;
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
        }
        
        .table td {
            vertical-align: middle;
            padding: 15px;
        }
        
        .action-btn {
            margin-right: 5px;
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
        <!-- Alertas -->
        <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                El horario seleccionado ya está ocupado. Por favor, elige otro.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                ¡Cita agendada con éxito!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="card-title mb-0"><i class="bi bi-calendar-check me-2"></i>Mis Citas</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCita">
                        <i class="bi bi-plus-circle me-1"></i> Nueva Cita
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de citas -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="bi bi-calendar-date me-1"></i> Fecha</th>
                                <th><i class="bi bi-clock me-1"></i> Hora</th>
                                <th><i class="bi bi-hospital me-1"></i> Centro de Salud</th>
                                <th><i class="bi bi-info-circle me-1"></i> Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($fila['fecha'])) ?></td>
                                    <td><?= date('H:i', strtotime($fila['horario'])) ?></td>
                                    <td><?= htmlspecialchars($fila['nombre_centro']) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $fila['estado'] ?>">
                                            <?= ucfirst(htmlspecialchars($fila['estado'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($fila['estado'] === 'pendiente'): ?>
                                            <a href="cambiar_cita.php?id_cita=<?= $fila['id_cita'] ?>" 
                                               class="btn btn-sm btn-outline-primary action-btn"
                                               title="Reprogramar">
                                               <i class="bi bi-calendar-event"></i>
                                            </a>
                                            
                                            <a href="cancelar_cita.php?id_cita=<?= $fila['id_cita'] ?>" 
                                               class="btn btn-sm btn-outline-danger action-btn"
                                               title="Cancelar"
                                               onclick="return confirm('¿Estás seguro de que quieres cancelar esta cita?');">
                                               <i class="bi bi-x-circle"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            
                            <?php if ($resultado->num_rows === 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                                        <p class="mt-2">No tienes citas programadas</p>
                                        <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalNuevaCita">
                                            <i class="bi bi-plus-circle me-1"></i> Agendar primera cita
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de nueva cita -->
    <?php include 'modal_cita.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>