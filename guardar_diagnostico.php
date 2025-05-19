<?php
session_start();
require_once 'conexion.php';

$id_cita = $_POST['id_cita'];
$id_usuario = $_POST['id_usuario'];
$id_medico = $_SESSION['id_medico'];

$edad = $_POST['edad'];
$curp = $_POST['curp'];
$tipo_sangre = $_POST['tipo_sangre'];
$telefono = $_POST['telefono'];
$telefono_emergencia = $_POST['telefono_emergencia'];
$calle = $_POST['calle'];
$colonia = $_POST['colonia'];
$numero = $_POST['numero'];
$ciudad = $_POST['ciudad'];
$diagnostico = $_POST['diagnostico'];

// Guardar diagnóstico
$sql_diag = "INSERT INTO diagnostico (id_cita, id_medico, diagnostico, prescripcion)
             VALUES (?, ?, ?, '')"; // puedes agregar prescripción si lo deseas
$stmt = $conn->prepare($sql_diag);
$stmt->bind_param("iis", $id_cita, $id_medico, $diagnostico);
$stmt->execute();
$stmt->close();

// Actualizar o insertar expediente
$sql_check = "SELECT id_expediente FROM expediente WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $sql_update = "UPDATE expediente SET edad=?, curp=?, tipo_sangre=?, telefono=?, telefono_emergencia=?, calle=?, colonia=?, numero=?, ciudad=? WHERE id_usuario=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("issssssssi", $edad, $curp, $tipo_sangre, $telefono, $telefono_emergencia, $calle, $colonia, $numero, $ciudad, $id_usuario);
} else {
    $sql_insert = "INSERT INTO expediente (id_usuario, edad, curp, tipo_sangre, telefono, telefono_emergencia, calle, colonia, numero, ciudad)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("iissssssss", $id_usuario, $edad, $curp, $tipo_sangre, $telefono, $telefono_emergencia, $calle, $colonia, $numero, $ciudad);
}


// ✅ Marcar la cita como completada
$sql_estado = "UPDATE cita SET estado = 'completada' WHERE id_cita = ?";
$stmt = $conn->prepare($sql_estado);
$stmt->bind_param("i", $id_cita);
$stmt->execute();
$stmt->close();
$conn->close();
header("Location: citas_medico.php?diagnostico=ok");
exit;
