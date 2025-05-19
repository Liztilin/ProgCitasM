<?php
// Puedes incluir este archivo donde quieras mostrar el modal
// Asegúrate de tener $conn disponible
$centros = $conn->query("SELECT id_centro, nombre_centro FROM centro_salud");
?>

<div class="modal fade" id="modalNuevaCita" tabindex="-1" aria-labelledby="modalNuevaCitaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="guardar_cita.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Sacar Nueva Cita</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">

        <div class="mb-3">
            <label class="form-label">Centro de Salud</label>
            <select name="centro" class="form-select" required>
                <option value="">Seleccione uno...</option>
                <?php while ($centro = $centros->fetch_assoc()): ?>
                    <option value="<?= $centro['id_centro'] ?>"><?= $centro['nombre_centro'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="date" name="fecha" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Hora</label>
            <select name="hora" id="horario" class="form-select" required>
                <option value="">Seleccione primero el centro y la fecha</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Medio de notificación</label>
            <select name="medio" class="form-select" required>
                <option value="email">Correo electrónico</option>
                <option value="whatsapp">WhatsApp</option>
            </select>
        </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Agendar Cita</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fechaInput = document.querySelector('input[name="fecha"]');
    const centroInput = document.querySelector('select[name="centro"]');
    const horarioSelect = document.getElementById('horario');

    function cargarHorariosDisponibles() {
        const fecha = fechaInput.value;
        const centro = centroInput.value;

        if (fecha && centro) {
            fetch(`horarios_disponibles.php?fecha=${fecha}&centro=${centro}`)
                .then(response => response.json())
                .then(data => {
                    horarioSelect.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(hora => {
                            const option = document.createElement('option');
                            option.value = hora;
                            option.textContent = hora;
                            horarioSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.textContent = 'No hay horarios disponibles';
                        horarioSelect.appendChild(option);
                    }
                });
        }
    }

    fechaInput.addEventListener('change', cargarHorariosDisponibles);
    centroInput.addEventListener('change', cargarHorariosDisponibles);
});
</script>
