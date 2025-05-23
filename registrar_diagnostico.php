<?php
session_start();
require_once 'conexion.php';

$id_cita = $_GET['id_cita'] ?? null;

if (!$id_cita) {
    echo "ID de cita no especificado.";
    exit;
}

// Obtener ID de usuario y nombre
$sql = "SELECT c.id_usuario, u.nombre, u.apellido_p, u.apellido_m
        FROM cita c
        JOIN usuario u ON c.id_usuario = u.id_usuario
        WHERE c.id_cita = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cita);
$stmt->execute();
$res = $stmt->get_result();
$paciente = $res->fetch_assoc();
$stmt->close();

if (!$paciente) {
    echo "Cita no encontrada.";
    exit;
}

$id_usuario = $paciente['id_usuario'];
$nombre_completo = $paciente['nombre'] . " " . $paciente['apellido_p'] . " " . $paciente['apellido_m'];

// Obtener expediente
$sql_expediente = "SELECT * FROM expediente WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_expediente);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res_exp = $stmt->get_result();
$expediente = $res_exp->fetch_assoc() ?: [];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Médico</title>
    <style>
        body {
            background-color: #dbeafe;
            font-family: Arial, sans-serif;
            padding: 20px;
            margin: 0;
        }
        .formulario {
            max-width: 1200px;
            margin: auto;
            background-color: #bfdbfe;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 2rem;
            color: #1e3a8a;
            margin-bottom: 30px;
        }
        h2 {
            font-size: 1.25rem;
            color: #1e40af;
            margin-bottom: 15px;
        }
        .contenedor {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        @media (min-width: 768px) {
            .contenedor {
                flex-direction: row;
            }
        }
        .seccion {
            flex: 1;
        }
        .campo {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #374151;
            font-size: 0.9rem;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 2px 2px 5px #e0e7ff;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #3b82f6;
        }
        textarea {
            resize: vertical;
        }
        .diagnostico {
            margin-top: 30px;
        }
        .nota {
            margin-top: 20px;
            font-size: 0.875rem;
            color: #4b5563;
        }
        .boton-contenedor {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        button {
            background-color: #3d44c4;
            color: white;
            font-weight: bold;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, transform 0.2s;
        }
        button:hover {
            background-color: #040478;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
<form class="formulario" action="guardar_diagnostico.php" method="POST">
    <h1>Historial Médico</h1>

    <input type="hidden" name="id_cita" value="<?= $id_cita ?>">
    <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">

    <div class="contenedor">
        <div class="seccion">
            <h2>Información del Paciente</h2>

            <div class="campo">
                <label>Nombre Completo *</label>
                <input type="text" name="nombre_completo" value="<?= htmlspecialchars($nombre_completo) ?>" readonly>
            </div>

            <div class="campo">
                <label>Edad *</label>
                <input type="number" name="edad" value="<?= htmlspecialchars($expediente['edad'] ?? '') ?>" min="0" max="120" required>
            </div>

            <div class="campo">
                <label>CURP *</label>
                <input type="text" name="curp" value="<?= htmlspecialchars($expediente['curp'] ?? '') ?>" required>
            </div>

            <div class="campo">
                <label>Teléfono</label>
                <input type="tel" name="telefono" value="<?= htmlspecialchars($expediente['telefono'] ?? '') ?>">
            </div>

            <div class="campo">
                <label>Tipo de Sangre *</label>
                <select name="tipo_sangre" required>
                    <option value="">Seleccionar</option>
                    <?php
                    $tipos = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
                    foreach ($tipos as $tipo) {
                        $sel = ($expediente['tipo_sangre'] ?? '') === $tipo ? 'selected' : '';
                        echo "<option value='$tipo' $sel>$tipo</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="seccion">
            <h2>Dirección</h2>

            <div class="campo">
                <label>Calle</label>
                <input type="text" name="calle" value="<?= htmlspecialchars($expediente['calle'] ?? '') ?>">
            </div>

            <div class="campo">
                <label>Colonia</label>
                <input type="text" name="colonia" value="<?= htmlspecialchars($expediente['colonia'] ?? '') ?>">
            </div>

            <div class="campo">
                <label>Número</label>
                <input type="text" name="numero" value="<?= htmlspecialchars($expediente['numero'] ?? '') ?>">
            </div>

            <div class="campo">
                <label>Ciudad</label>
                <input type="text" name="ciudad" value="<?= htmlspecialchars($expediente['ciudad'] ?? '') ?>">
            </div>

            <div class="campo">
                <label>Teléfono de Emergencia</label>
                <input type="tel" name="telefono_emergencia" value="<?= htmlspecialchars($expediente['telefono_emergencia'] ?? '') ?>">
            </div>
        </div>
    </div>

    <div class="diagnostico">
        <h2>Diagnóstico</h2>
        <textarea name="diagnostico" rows="6" required></textarea>
    </div>
    <div class="diagnostico">
        <h2>Prescripción</h2>
        <textarea name="prescripcion" rows="4" placeholder="Ingrese la prescripción si aplica..."></textarea>
    </div>

    <div class="boton-contenedor">
        <button type="submit">Guardar</button>
    </div>
</form>
</body>
</html>
