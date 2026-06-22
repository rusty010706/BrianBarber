<?php
session_start();
require_once 'includes/conexions.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre   = $_POST['nombre'];
    $correo   = $_POST['correo'];
    $password = $_POST['pass'];  
    $password_conf = $_POST['pass_conf'];
    $apellidos = $_POST['apellidos'];
    $tarjeta  = !empty($_POST['tarjeta']) ? $_POST['tarjeta'] : ""; 

    // 1. Verificación de contraseñas iguales
    if ($password !== $password_conf) {
        echo "<script>
                alert('Les contrasenyes no coincideixen. Si us plau, verifica les dades.');
                window.history.back();
              </script>";
        exit();
    }

    // 2. Verificación real de correo en la BBDD 
    $sql_check = "SELECT correo FROM usuarios WHERE correo = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "s", $correo);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        echo "<script>
                alert('El correo registrado ya esta en uso. Revise que no tenga cuenta o restablezca la contraseña, gracias');
                window.history.back();
              </script>";
        mysqli_stmt_close($stmt_check);
        exit();
    }
    mysqli_stmt_close($stmt_check);

    // 3. Preparación de datos y Transacción 
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    mysqli_begin_transaction($conn);

    try {
        // 4. Insertar en la tabla 'usuarios' 
        $sql_u = "INSERT INTO usuarios (correo, contraseña, nombre, apellidos, es_trabajador) VALUES (?, ?, ?, ?, 0)";
        $stmt_u = mysqli_prepare($conn, $sql_u);
        mysqli_stmt_bind_param($stmt_u, "ssss", $correo, $password_hash, $nombre, $apellidos);
        mysqli_stmt_execute($stmt_u);

        // Recuperamos el ID recién generado 
        $id_usuario = mysqli_insert_id($conn);

        // 5. Insertar en la tabla 'clientes' 
        $sql_c = "INSERT INTO clientes (tarjeta_credito, usuarios_id_usuario) VALUES (?, ?)";
        $stmt_c = mysqli_prepare($conn, $sql_c);
        mysqli_stmt_bind_param($stmt_c, "si", $tarjeta, $id_usuario);
        mysqli_stmt_execute($stmt_c);

        // Confirmamos cambios
        mysqli_commit($conn);
        
        // Redirigimos directamente (sin echo previo para evitar errores)
        header("Location: index.php?registro=exito");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error al guardar en la BBDD: " . $e->getMessage();
    }
}

mysqli_close($conn);
?>
