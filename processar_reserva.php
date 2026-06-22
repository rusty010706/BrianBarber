<?php
session_start();
require_once 'includes/conexions.php'; 

// Activamos el reporte estricto de errores de MySQL
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validar sesión
    if (!isset($_SESSION['id_usuario'])) {
        die("Error: No has iniciat sessió correctament o la sessió ha caducat.");
    }
    
    // 2. Validar que no falten datos del formulario
    if (empty($_POST['data_reserva']) || empty($_POST['hora']) || empty($_POST['tipus_tall']) || empty($_POST['trabajador'])) {
        die("Error: Falten dades obligatòries al formulari.");
    }

    $id_usuario_logueado = $_SESSION['id_usuario'];

    // 3. Buscar el id_cliente REAL asociado a este usuario
    $sql_cliente = "SELECT id_cliente FROM clientes WHERE usuarios_id_usuario = ?";
    $stmt_cliente = mysqli_prepare($conn, $sql_cliente);
    mysqli_stmt_bind_param($stmt_cliente, "i", $id_usuario_logueado);
    mysqli_stmt_execute($stmt_cliente);
    $result_cliente = mysqli_stmt_get_result($stmt_cliente);

    if ($row_cliente = mysqli_fetch_assoc($result_cliente)) {
        $id_cliente_real = $row_cliente['id_cliente'];
    } else {
        die("Error: El teu usuari no està registrat com a client a la base de dades.");
    }

    // 4. Recoger los datos del formulario (Aquí estaba el fallo, aseguramos que todas existan)
    $data = $_POST['data_reserva'];
    $hora = $_POST['hora'];
    $tall = $_POST['tipus_tall']; 
    $id_trab = $_POST['trabajador']; // <-- El ID del barbero
    $tissora = isset($_POST['extra_tisora']) ? 1 : 0;
    
    // 5. Validar que la fecha no sea del pasado
    if ($data < date("Y-m-d")) {
        echo "
        <script>
            alert('La data no pot ser anterior a la data actual'); 
            window.history.back();
        </script>";
        exit();
    }

    // 6. Intentar guardar en la Base de Datos
    try {
        mysqli_begin_transaction($conn);

        $sql_citas = "INSERT INTO citas (hora, fecha, clientes_id_cliente, empleados_id_empleado, tall, tissores) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_citas = mysqli_prepare($conn, $sql_citas);
        
        // ssiisi = string(hora), string(fecha), int(cliente), int(empleado), string(tall), int(tissores)
        mysqli_stmt_bind_param($stmt_citas, "ssiisi", $hora, $data, $id_cliente_real, $id_trab, $tall, $tissora);
        mysqli_stmt_execute($stmt_citas);

        // Si todo va bien, confirmamos y redirigimos
        mysqli_commit($conn);
        header("Location: perfil.php?reserva=exito");
        exit();

    } catch (Throwable $e) {
        // Si algo falla, revertimos y mostramos el error
        mysqli_rollback($conn);
        echo "<div style='font-family: Arial; padding: 20px; background: #ffebee; color: #c62828; border-radius: 5px; margin: 20px;'>";
        echo "<b>Error fatal al guardar la reserva en la BBDD:</b><br>" . $e->getMessage();
        echo "</div>";
    }
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>