<?php
session_start();

require_once 'includes/conexions.php'; 

// 2. Verificar datos por POST
if (!isset($_POST['correo']) || !isset($_POST['password'])) {
    die("Por favor, introduce usuario y contraseña.");
}

$user = $_POST['correo'];
$pass = $_POST['password'];

$sql = "SELECT id_usuario, nombre, contraseña, es_trabajador FROM usuarios WHERE correo = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "s", $user);
mysqli_stmt_execute($stmt);
$resultat = mysqli_stmt_get_result($stmt);

if ($dades_usuaris = mysqli_fetch_assoc($resultat)) {
    
    // 4. Verificación de contraseña
    if (password_verify($pass, $dades_usuaris['contraseña'])) {
        
        session_regenerate_id(true);

        // Guardamos los datos usando las claves correctas de la tabla 
        $_SESSION['id_usuario'] = $dades_usuaris['id_usuario'];
        $_SESSION['usuario_nombre'] = $dades_usuaris['nombre'];
        $_SESSION['empleado'] = $dades_usuaris['es_trabajador'];

        header('Location: index.php');
        exit(); 
        
    } else {
        echo "
        <script>
            alert('Usuario o contraseña incorrectos'); 
            window.history.back();
        </script>";
    }
} else {
    echo "
        <script>
            alert('Usuario o contraseña incorrectos'); 
            window.history.back();
        </script>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
