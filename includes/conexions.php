<?php

/**
 * Función para obtener la conexión a la base de datos.
 * @param string
 */
function conectar_db($perfil = 'admin') {
    $servidor = "mysql-8001.dinaserver.com";
    $base_datos = "brianbarber";

    /*los usuarios según el perfil*/
    if ($perfil == 'lector') {
        $usuario = "lector_brian"; 
        $password = "pass_lector_123";
    } else {
        // Perfil por defecto (admin/escritura)
        $usuario = "kinybdn"; 
        $password = "Brianpederasta69!";
    }

    // Crear conexión
    $conexion = mysqli_connect($servidor, $usuario, $password, $base_datos);

    // Verificar conexión
    if (!$conexion) {
        die("Fallo crítico en la conexión: " . mysqli_connect_error());
    }

    // Forzar UTF-8 para evitar problemas con ñ y tildes
    mysqli_set_charset($conexion, "utf8");

    return $conexion;
}

// variable para usar la funcion en otros achivos
$conn = conectar_db(); 

// Inicialització dinàmica silenciosa (Migració de base de dades)
try {
    @mysqli_query($conn, "ALTER TABLE servicios ADD COLUMN categoria VARCHAR(50) DEFAULT 'Bàsic'");
    @mysqli_query($conn, "ALTER TABLE servicios ADD COLUMN descripcion TEXT");
    @mysqli_query($conn, "ALTER TABLE empleados ADD COLUMN especialitat_desc TEXT");


} catch (\Throwable $e) {
    // Ignorem silenciosament els errors de columnes duplicades si l'esquema ja està actualitzat
}
?>