<?php
session_start();

if (!isset($_SESSION['id_medico'])) {
    header("Location: login_medico.php");
    exit;
}

require_once 'conexion.php';

$id_medico = $_SESSION['id_medico'];

// Obtener el centro de salud del médico
$sql_centro = "SELECT id_centro, nombre, apellido_p, apellido_m FROM medico WHERE id_medico = ?";
$stmt_centro = $conn->prepare($sql_centro);
$stmt_centro->bind_param("i", $id_medico);
$stmt_centro->execute();
$result = $stmt_centro->get_result();
$medico_data = $result->fetch_assoc();
$id_centro = $medico_data['id_centro'];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas Médico - Sanacita</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --light-blue: #e3f2fd;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
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
        
        .status-completada {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-cancelada {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .date-picker-card {
            background-color: var(--light-blue);
        }
        
        .btn-action {
            min-width: 100px;
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
                    <h4 class="mb-0">Sanacita - Panel Médico</h4>
                </div>
                <div class="d-flex align-items-center">
                    <span class="me-3">Dr. <?= htmlspecialchars($medico_data['nombre'] . ' ' . $medico_data['apellido_p']) ?></span>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($medico_data['nombre'] . '+' . $medico_data['apellido_p']) ?>&background=random" alt="User" class="user-avatar">
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="card-title mb-0"><i class="bi bi-calendar-check me-2"></i>Citas Médicas</h2>
                        <p class="text-muted mb-0">Gestión de consultas programadas</p>
                    </div>
                    <div class="d-flex">
                        <a href="logout/logout_medico.php" class="btn btn-outline-danger me-2">
                            <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selector de fecha -->
        <div class="card date-picker-card mb-4">
            <div class="card-body">
                <form method="GET" class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label for="fecha" class="form-label fw-bold">Seleccionar fecha de consulta:</label>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <input type="date" name="fecha" id="fecha" class="form-control" value="<?= $fecha_consulta ?>">
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado de citas -->
        <?php if ($citas->num_rows > 0): ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="bi bi-person me-1"></i> Paciente</th>
                                    <th><i class="bi bi-calendar-date me-1"></i> Fecha</th>
                                    <th><i class="bi bi-clock me-1"></i> Hora</th>
                                    <th><i class="bi bi-info-circle me-1"></i> Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($cita = $citas->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cita['nombre'] . ' ' . $cita['apellido_p'] . ' ' . $cita['apellido_m']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($cita['fecha'])) ?></td>
                                        <td><?= date('H:i', strtotime($cita['horario'])) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower($cita['estado']) ?>">
                                                <?= ucfirst(htmlspecialchars($cita['estado'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="registrar_diagnostico.php?id_cita=<?= $cita['id_cita'] ?>" 
                                                   class="btn btn-sm btn-success btn-action me-2">
                                                   <i class="bi bi-file-earmark-medical me-1"></i>Diagnóstico
                                                </a>
                                                <a href="historial_clinico.php?id_usuario=<?= $cita['id_usuario'] ?>" 
                                                   class="btn btn-sm btn-info btn-action">
                                                   <i class="bi bi-file-text me-1"></i>Historial
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <h4>No hay citas programadas</h4>
                    <p>No se encontraron citas para la fecha seleccionada.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Actualizar automáticamente al cambiar la fecha (opcional)
        document.getElementById('fecha').addEventListener('change', function() {
            if(this.value) {
                this.form.submit();
            }
        });
    </script>
</body>
</html>