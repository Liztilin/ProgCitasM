<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT id_notificacion, titulo, mensaje, fecha, leido, tipo 
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Sanacita</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
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
        
        .notification-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
            background-color: white;
        }
        
        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .notification-unread {
            border-left-color: var(--warning-color);
            background-color: #fffcf5;
        }
        
        .notification-important {
            border-left-color: var(--danger-color);
        }
        
        .notification-opportunity {
            border-left-color: var(--success-color);
        }
        
        .notification-info {
            border-left-color: var(--info-color);
        }
        
        .notification-time {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .notification-badge {
            position: absolute;
            right: 15px;
            top: 15px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
        
        .mark-all-btn {
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.9rem;
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
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="card-title mb-0"><i class="bi bi-bell me-2"></i>Notificaciones</h2>
                        <p class="text-muted mb-0">Tus alertas y mensajes importantes</p>
                    </div>
                    <button class="btn btn-sm btn-outline-primary mark-all-btn">
                        <i class="bi bi-check-all me-1"></i>Marcar todas como leídas
                    </button>
                </div>
            </div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="notification-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="notification-card p-3 position-relative <?= !$row['leido'] ? 'notification-unread' : '' ?> 
                        <?= isset($row['tipo']) ? 'notification-' . $row['tipo'] : '' ?>">
                        <?php if (!$row['leido']): ?>
                            <span class="badge bg-warning notification-badge">Nuevo</span>
                        <?php endif; ?>
                        
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <?php if (isset($row['tipo'])): ?>
                                    <?php if ($row['tipo'] == 'important'): ?>
                                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 1.5rem;"></i>
                                    <?php elseif ($row['tipo'] == 'opportunity'): ?>
                                        <i class="bi bi-star-fill text-success" style="font-size: 1.5rem;"></i>
                                    <?php else: ?>
                                        <i class="bi bi-info-circle-fill text-primary" style="font-size: 1.5rem;"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="bi bi-bell-fill text-primary" style="font-size: 1.5rem;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?= htmlspecialchars($row['titulo']) ?></h5>
                                <p class="mb-2"><?= htmlspecialchars($row['mensaje']) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="notification-time">
                                        <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($row['fecha'])) ?>
                                    </span>
                                    <?php if (isset($row['tipo']) && $row['tipo'] == 'opportunity'): ?>
                                        <span class="badge bg-success">Oportunidad</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="empty-state">
                    <i class="bi bi-bell-slash"></i>
                    <h4>No hay notificaciones</h4>
                    <p>No tienes notificaciones en este momento. Te avisaremos cuando tengas nuevas actualizaciones.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para marcar todas como leídas (simulada)
        document.querySelector('.mark-all-btn').addEventListener('click', function() {
            // Aquí iría la lógica para marcar todas como leídas
            alert('Todas las notificaciones se marcarán como leídas. Esta es una simulación.');
            
            // Actualizar la interfaz
            document.querySelectorAll('.notification-unread').forEach(card => {
                card.classList.remove('notification-unread');
                const badge = card.querySelector('.notification-badge');
                if (badge) badge.remove();
            });
        });
    </script>
</body>
</html>