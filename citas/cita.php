<?php
include 'php/conexion.php';

$mensaje = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servicio_id = $_POST['servicio'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $anticipo = floatval($_POST['anticipo'] ?? 0);

    if (!$servicio_id || !$fecha || !$hora || !$nombre || !$telefono || !$correo) {
        $error = "Por favor completa todos los campos.";
    } else {
        // Buscar cliente por correo o teléfono
        $stmt = $conn->prepare("SELECT id FROM clientes WHERE correo = ? OR telefono = ?");
        $stmt->bind_param("ss", $correo, $telefono);
        $stmt->execute();
        $stmt->bind_result($cliente_id);
        if ($stmt->fetch()) {
            $stmt->close();
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO clientes (nombre, correo, telefono) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nombre, $correo, $telefono);
            if (!$stmt->execute()) {
                $error = "Error al guardar cliente: " . $stmt->error;
            } else {
                $cliente_id = $stmt->insert_id;
            }
            $stmt->close();
        }

        if (!$error) {
            $fecha_hora = $fecha . ' ' . $hora . ':00';

            $stmt = $conn->prepare("SELECT precio FROM servicios WHERE id = ?");
            $stmt->bind_param("i", $servicio_id);
            $stmt->execute();
            $stmt->bind_result($precio_total);
            $stmt->fetch();
            $stmt->close();

            if ($anticipo > $precio_total) {
                $error = "El anticipo no puede ser mayor que el precio total del servicio ($precio_total).";
            } else {
                $stmt = $conn->prepare("INSERT INTO citas (id_cliente, id_servicio, fecha_hora, monto_total, anticipo, status) VALUES (?, ?, ?, ?, ?, 'Pendiente')");
                //$stmt->bind_param("iisd", $cliente_id, $servicio_id, $fecha_hora, $precio_total, $anticipo);
                $stmt->bind_param("iisdd", $cliente_id, $servicio_id, $fecha_hora, $precio_total, $anticipo);
                if ($stmt->execute()) {
                    $mensaje = "Cita reservada con éxito.";
                } else {
                    $error = "Error al guardar la cita: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Consultar servicios para el select
$sql = "SELECT id, nombre, descripcion, duracion, precio FROM servicios";
$result = $conn->query($sql);

$servicios = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $servicios[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agendar Cita - Estética</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Sofia|Audiowide" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;700&display=swap" />
    <link rel="stylesheet" href="/estetica/citas/estile/cita.css" />
</head>

<body>
    <header>
        <img src="../img/logo.jpg" alt="logotipo" />
        <h1>Agenda tu cita</h1>
        <nav>
            <a href="../index.html"><i class="fas fa-home"></i> Inicio</a>
            <a href="../index.html"><i class="fas fa-cut"></i> Servicios</a>
            <a href="#ubicacion"><i class="fas fa-map-marker-alt"></i> Ubicación</a>
            <a href="#contacto"><i class="fas fa-envelope"></i> Contacto</a>
        </nav>
    </header>

    <main>
        <?php if ($mensaje): ?>
            <p style="color: green; font-weight: bold;"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form id="form-cita" method="post" action="">
            <label for="servicio">Servicio:</label>
            <select id="servicio" name="servicio" required onchange="mostrarDatosServicio()">
                <option value="">Selecciona un servicio</option>
                <?php foreach ($servicios as $servicio): ?>
                    <option value="<?= htmlspecialchars($servicio['id']) ?>"
                        data-precio="<?= htmlspecialchars($servicio['precio']) ?>"
                        data-duracion="<?= htmlspecialchars($servicio['duracion']) ?>"
                        data-descripcion="<?= htmlspecialchars($servicio['descripcion']) ?>">
                        <?= htmlspecialchars($servicio['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div id="info-servicio" style="display:none; margin-top: 10px; border: 1px solid #ddd; padding: 10px;">
                <p><strong>Descripción:</strong> <span id="desc-servicio"></span></p>
                <p><strong>Duración:</strong> <span id="duracion-servicio"></span> minutos</p>
                <p><strong>Precio:</strong> $<span id="precio-servicio"></span></p>
            </div>

            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required />

            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" required />

            <label for="correo">Correo electrónico:</label>
            <input type="email" id="correo" name="correo" required />

            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" required />

            <label for="hora">Hora:</label>
            <input type="time" id="hora" name="hora" required />

            <label for="anticipo">Anticipo (monto mínimo):</label>
            <input type="number" id="anticipo" name="anticipo" min="0" step="0.01" required />

            <button type="submit">Reservar</button>
        </form>
    </main>

    <br />
    <footer>
        <div class="footer-container">
            <div class="contacto">
                <h3>Contacto</h3>
                <p><i class="fas fa-phone"></i> Teléfono: 729-169-3310</p>
                <p><i class="fas fa-envelope"></i> Email: ramirezeli123ei@gmail.com</p>
                <a href="https://www.facebook.com/profile.php?id=61567247588171" target="_blank">
                    <i class="fab fa-facebook"></i> Síguenos en Facebook
                </a>
            </div>
            <div class="ubicacion">
                <h3>Ubicación</h3>
                <p><i class="fas fa-map-marker-alt"></i> Frente a la Deportiva Judicial de San Lorenzo Tepaltitlan.</p>
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d235.3268284777127!2d-99.61659652956929!3d19.315855713719465!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ses!2smx!4v1743120359149!5m2!1ses!2smx"
                    width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade" width="100" height="80" style="border:0;"
                    allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </footer>

    <script>
        function mostrarDatosServicio() {
            const select = document.getElementById('servicio');
            const opcion = select.options[select.selectedIndex];
            if (!opcion.value) {
                document.getElementById('info-servicio').style.display = 'none';
                return;
            }
            document.getElementById('desc-servicio').textContent = opcion.getAttribute('data-descripcion');
            document.getElementById('duracion-servicio').textContent = opcion.getAttribute('data-duracion');
            document.getElementById('precio-servicio').textContent = opcion.getAttribute('data-precio');
            document.getElementById('info-servicio').style.display = 'block';
        }
    </script>
</body>

</html>