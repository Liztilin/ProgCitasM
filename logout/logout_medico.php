<?php
session_start();

// Eliminar solo las variables de sesión del médico
unset($_SESSION['id_medico']);
unset($_SESSION['nombre_medico']);
unset($_SESSION['email_medico']);

// Destruir la sesión si ya no hay otras variables activas
if (empty($_SESSION)) {
    session_destroy();
}

// Redirigir al login del médico
header("Location: ../login_medico.php");
exit;
