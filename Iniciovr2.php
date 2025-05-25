<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sanacita - Carrusel 3D</title>
    <!-- Glide.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.5.0/css/glide.core.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.5.0/css/glide.theme.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="Inicio vr2.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <!-- Botón de menú -->
            <div class="logo-container">
                <button id="menuBtn" class="menu-btn"><i class="bi bi-list"></i></button>
            </div>

            <!-- Bienvenida y avatar -->
            <div class="user-nav">
                <span class="welcome-text">Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['usuario_nombre'] ?? 'Usuario') ?>&background=random" alt="Usuario" class="user-avatar">
            </div>
        </div>
    </header>

<!-- Menú Lateral -->
    <aside id="sidebar" class="sidebar">
        <nav class="menu">
            <ul>
                <li><i class="bi bi-calendar2-check"></i> <a href="citas.php">Citas</a></li>
                <li><i class="bi bi-bell"></i> <a href="notificaciones.php">Notificaciones</a></li>
                <li><i class="bi bi-file-medical"></i> <a href="historial_clinico_usuario.php">Historial Médico</a></li>
            </ul>
        </nav>
    </aside>
    <!-- Main Content -->
    <main class="main-content">
        <div class="intro">
            <img src="Imagenes progsanacita/logo_sanacita2.png" alt="Sanacita Logo" class="logo">
            <h1 class="title">Sanacita</h1>
            <p class="subtitle">Descubre los mejores centros médicos y agenda tu cita en segundos</p>
        </div>
        
        <!-- Carrusel 3D con Glide.js -->
        <div class="glide">
            <div class="glide__track" data-glide-el="track">
                <ul class="glide__slides">
                    <li class="glide__slide">
                        <img src="Imagenes progsanacita/nvaobrera.jpg" alt="Centro de Salud Nueva Obrera">
                        <div class="slide-content">
                            <h3 class="slide-title">Centro de Salud Nueva Obrera</h3>
                            <span class="slide-hours"><i class="bi bi-clock"></i> 9:00 AM - 6:00 PM</span>
                            <p class="slide-desc">Consultorios De Medicina General Del Sector Público.</p>
                            <a href="#" 
                            class="btn-agendar" 
                            data-id-centro="1" 
                            data-nombre-centro="Centro de Salud Nueva Obrera"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalNuevaCita">
                            <i class="bi bi-calendar-plus"></i> Agendar Cita
                            </a>
                        </div>
                    </li>
                    <li class="glide__slide">
                        <img src="Imagenes progsanacita/carish.jpg" alt="Centro Radiologico Carish">
                        <div class="slide-content">
                            <h3 class="slide-title">Centro Radiologico Carish</h3>
                            <span class="slide-hours"><i class="bi bi-clock"></i> 8:00 AM - 7:00 PM</span>
                            <p class="slide-desc">Clinica especialiasta en radiologia, traumatologia y salud ocupacional.</p>
                            <a href="#" 
                            class="btn-agendar" 
                            data-id-centro="2" 
                            data-nombre-centro="Centro Radiologico Carish"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalNuevaCita">
                            <i class="bi bi-calendar-plus"></i> Agendar Cita
                            </a>
                        </div>
                    </li>
                    <li class="glide__slide">
                        <img src="Imagenes progsanacita/H36.jpg" alt="Hospital General Zona 36">
                        <div class="slide-content">
                            <h3 class="slide-title">Hospital General Zona 36</h3>
                            <span class="slide-hours"><i class="bi bi-clock"></i> Abierto 24/7</span>
                            <p class="slide-desc">Urgencias y tratamientos especializados con atención continua.</p>
                            <a href="#" 
                            class="btn-agendar" 
                            data-id-centro="4" 
                            data-nombre-centro="Hospital General Zona 36"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalNuevaCita">
                            <i class="bi bi-calendar-plus"></i> Agendar Cita
                            </a>
                        </div>
                    </li>
                    <li class="glide__slide">
                        <img src="Imagenes progsanacita/teresa.jpg" alt="Centro de Salud Teresa Morales">
                        <div class="slide-content">
                            <h3 class="slide-title">Centro de Salud Teresa Morales</h3>
                            <span class="slide-hours"><i class="bi bi-clock"></i> 9:00 AM - 6:00 PM</span>
                            <p class="slide-desc">Especialistas en medicina preventiva con programas de salud familiar y comunitaria.</p>
                            <a href="#" 
                            class="btn-agendar" 
                            data-id-centro="3" 
                            data-nombre-centro="Centro de Salud Teresa Morales"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalNuevaCita">
                            <i class="bi bi-calendar-plus"></i> Agendar Cita
                            </a>
                        </div>
                    </li>
                    <li class="glide__slide">
                        <img src="Imagenes progsanacita/centro_integral.png" alt="Centro Integral Para La Salud">
                        <div class="slide-content">
                            <h3 class="slide-title">Centro Integral Para La Salud</h3>
                            <span class="slide-hours"><i class="bi bi-clock"></i> 9:00 AM - 06:00 PM</span>
                            <p class="slide-desc">Ofrece servicios medicos de varias especialides, incluyendo fisioterapia.</p>
                            <a href="#" 
                            class="btn-agendar" 
                            data-id-centro="5" 
                            data-nombre-centro="Centro Integral Para La Salud"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalNuevaCita">
                            <i class="bi bi-calendar-plus"></i> Agendar Cita
                            </a>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="glide__arrows" data-glide-el="controls">
                <button class="glide__arrow glide__arrow--left" data-glide-dir="<"><i class="bi bi-chevron-left"></i></button>
                <button class="glide__arrow glide__arrow--right" data-glide-dir=">"><i class="bi bi-chevron-right"></i></button>
            </div>

            <div class="glide__bullets" data-glide-el="controls[nav]">
                <button class="glide__bullet" data-glide-dir="=0"></button>
                <button class="glide__bullet" data-glide-dir="=1"></button>
                <button class="glide__bullet" data-glide-dir="=2"></button>
                <button class="glide__bullet" data-glide-dir="=3"></button>
                <button class="glide__bullet" data-glide-dir="=4"></button>
                <button class="glide__bullet" data-glide-dir="=5"></button>
            </div>
        </div>
    </main>

    <!-- Glide.js Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.5.0/glide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Inicializar el carrusel 3D
        new Glide('.glide', {
            type: 'carousel',
            perView: 3,
            focusAt: 'center',
            gap: 20,
            breakpoints: {
                992: {
                    perView: 2
                },
                768: {
                    perView: 1
                }
            }
        }).mount();
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            mainContent.classList.toggle('shift');
        });

        //  Cerrar el menú al salir el cursor
        sidebar.addEventListener('mouseleave', () => {
            sidebar.classList.remove('open');
            mainContent.classList.remove('shift');
        });
    </script>
    
    <?php include 'modal_cita.php'; ?>
</body>
</html>