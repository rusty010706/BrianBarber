<?php
session_start();
require_once 'includes/conexions.php';

$noms_dies = [
    1 => 'Dilluns',
    2 => 'Dimarts',
    3 => 'Dimecres',
    4 => 'Dijous',
    5 => 'Divendres',
    6 => 'Dissabte',
    7 => 'Diumenge'
];

$sql_footer_horaris = "SELECT dia_setmana, hora_obertura, hora_tancament, es_tancat FROM horaris_barberia ORDER BY dia_setmana ASC";
$resultat_footer = @mysqli_query($conn, $sql_footer_horaris);

// 1. SEGURIDAD VITAL: Si no es empleado (valor 1), lo echamos de aquí.
if (!isset($_SESSION['empleado']) || $_SESSION['empleado'] != 1) {
  header('Location: index.php');
  exit();
}

$mensaje_cites = "";

// 1. Solo entramos aquí si se ha pulsado explícitamente el botón
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancelar_totes_cites'])) {
    
    $sql_del = "DELETE FROM citas WHERE fecha < CURRENT_DATE()";
   
    if ($stmt_del = mysqli_prepare($conn, $sql_del)) {
        
      if (mysqli_stmt_execute($stmt_del)) {
            $filas_borradas = mysqli_stmt_affected_rows($stmt_del);
            $mensaje_cites = "<p class='admin-msg-success'>$filas_borradas cites del passat han estat eliminades correctament.</p>";
        } else {
            $mensaje_cites = "<p class='admin-msg-error'>Error a l'eliminar les cites: " . mysqli_stmt_error($stmt_del) . "</p>";
        }
        
        mysqli_stmt_close($stmt_del);
        
    } else {
        $mensaje_cites = "<p class='admin-msg-error'>Error preparant la consulta a la base de dades.</p>";
    }
}
$mensaje_cancelar = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancelar_cita'])) {
    $fecha_canc = $_POST['fecha_canc'];
    $hora_canc = $_POST['hora_canc'];
    $cliente_canc = (int)$_POST['cliente_canc'];
    $empleado_canc = (int)$_POST['empleado_canc'];

    $sql_del = "DELETE FROM citas WHERE fecha = ? AND hora = ? AND clientes_id_cliente = ? AND empleados_id_empleado = ?";
    $stmt_del = mysqli_prepare($conn, $sql_del);
    mysqli_stmt_bind_param($stmt_del, "ssii", $fecha_canc, $hora_canc, $cliente_canc, $empleado_canc);
    
    if (mysqli_stmt_execute($stmt_del)) {
        $mensaje_cancelar = "<p class='admin-msg-success'>Cita cancel·lada i eliminada correctament.</p>";
    } else {
        $mensaje_cancelar = "<p class='admin-msg-error'>Error al cancel·lar la cita.</p>";
    }
}

// Data per defecte
$data_cites = isset($_GET['data']) ? $_GET['data'] : date("Y-m-d");

// Obtenir cites del dia ordenades per hora
$sql_cites = "
    SELECT 
        c.hora, c.fecha, c.tall, c.tissores, c.clientes_id_cliente, c.empleados_id_empleado,
        u_client.nombre AS client_nom, u_client.apellidos AS client_cognom, u_client.correo AS client_correu,
        u_treb.nombre AS treb_nom, u_treb.apellidos AS treb_cognom
    FROM citas c
    LEFT JOIN usuarios u_client ON c.clientes_id_cliente = u_client.id_usuario
    LEFT JOIN empleados e ON c.empleados_id_empleado = e.id_empleado
    LEFT JOIN usuarios u_treb ON e.usuarios_id_usuario = u_treb.id_usuario
    WHERE c.fecha = ?
    ORDER BY c.hora ASC
";

$stmt = mysqli_prepare($conn, $sql_cites);
mysqli_stmt_bind_param($stmt, "s", $data_cites);
mysqli_stmt_execute($stmt);
$resultado_cites = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="ca">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panell Administratiu | BrianBarber</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/ico" href="images/favicon.ico">
</head>

<body>

    <header class="header-main">
    <div class="header-container">
      <div class="logo">
        <a href="index.php" class="logo-link">
          <img src="images/logo.png" alt="Logo" id="logo_principal">
          <span class="logo-subtitle">Això és un projecte educatiu</span>
        </a>
      </div>
      
 <nav class="nav-main">
        <ul class="menu-principal">
          <li><a href="index.php">Inici</a></li>
          <li><a href="qui-som.php">Qui som</a></li>
          <li><a href="serveis.php">Serveis</a></li>

          <?php if(!isset($_SESSION['id_usuario'])): ?>
            <li><a href="accedir.php" class="btn-login-header">Iniciar sessió</a></li>
          <?php else: ?>
            <li><a href="reserves.php">Reserves</a></li>
            <li><a href="productes.php">Productes</a></li>
            <li><a href="resena.php">Deixa la teva resenya</a></li>
            <li class="menu-desplegable">
              <a href="#" class="menu-link">El meu compte <span class="flecha">▼</span></a>
          
              <ul class="submenu">
                <li><a href="perfil.php">El teu perfil</a></li>
            
                <?php if (isset($_SESSION['empleado']) && $_SESSION['empleado'] == 1): ?>
                  <li><a href="panell.php">Panell administrador</a></li>
                <?php endif; ?>
            
                <li><a href="logout.php" class="logout-link">Tanca la sessió</a></li>
              </ul>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>

  <main class="admin-container">

    <aside class="admin-sidebar">
      <div class="admin-user-info">
      <h3>Benvingut, <?php echo $_SESSION['usuario_nombre']; ?></h3>
        <p>Nivell: Administrador</p>
      </div>

      <ul class="admin-menu">
        <li><a href="panell.php">Resum (Dashboard)</a></li>
        <li><a href="panell_cites.php" class="active">Gestió de Cites</a></li>
        <li><a href="panell_usuaris.php">Gestió d'Usuaris</a></li>
        <li><a href="panell_serveis.php">Serveis i Preus</a></li>
      </ul>
    </aside>

    <section class="admin-content">
      <h2>Gestió de Cites / Calendari</h2>
      <p>Consulta les reserves d'un dia concret o cancel·la les cites dels usuaris si aquests t'ho demanen de manera externa.</p>
      
      <?php if (!empty($mensaje)) echo $mensaje; ?>

      <!-- Filter Form -->
      <form method="GET" action="panell_cites.php" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <label for="data_filtre" style="font-weight: 600;">Selecciona una data:</label>
        <input type="date" id="data_filtre" name="data" value="<?php echo htmlspecialchars($data_cites); ?>" class="admin-input" required>
        <button type="submit" class="btn-admin-add">Cercar Cites</button>
      </form>

      <div class="table-responsive">
        <table class="table-users">
          <thead>
            <tr>
              <th>Hora</th>
              <th>Client</th>
              <th>Correu Client</th>
              <th>Treballador Assignat</th>
              <th>Servei</th>
              <th class="center">Extres</th>
              <th class="center">Accions</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            if (mysqli_num_rows($resultado_cites) > 0): 
              while ($cita = mysqli_fetch_assoc($resultado_cites)): 
            ?>
            <tr>
              <td><strong><?php echo substr(htmlspecialchars($cita['hora']), 0, 5); ?></strong></td>
              <td><?php echo htmlspecialchars($cita['client_nom'] . ' ' . $cita['client_cognom']); ?></td>
              <td><?php echo htmlspecialchars($cita['client_correu']); ?></td>
              <td><?php echo htmlspecialchars($cita['treb_nom'] . ' ' . $cita['treb_cognom']); ?></td>
              <td><?php echo htmlspecialchars($cita['tall'] ?? 'No definit'); ?></td>
              <td class="center">
                <?php echo ($cita['tissores'] == 1) ? "<span class='label-supervisor' style='background-color:#3498db;'>Tissores</span>" : "--"; ?>
              </td>
              <td class="center">
                <form action="panell_cites.php?data=<?php echo urlencode($data_cites); ?>" method="POST" style="margin: 0; display: inline-block;" onsubmit="return confirm('N\'estàs segur de voler cancel·lar aquesta cita de manera permanent?');">
                    <input type="hidden" name="fecha_canc" value="<?php echo htmlspecialchars($cita['fecha']); ?>">
                    <input type="hidden" name="hora_canc" value="<?php echo htmlspecialchars($cita['hora']); ?>">
                    <input type="hidden" name="cliente_canc" value="<?php echo $cita['clientes_id_cliente']; ?>">
                    <input type="hidden" name="empleado_canc" value="<?php echo $cita['empleados_id_empleado']; ?>">
                    <button type="submit" name="cancelar_cita" class="btn-admin-remove-worker">Cancel·lar Cita</button>
                </form>
              </td>
            </tr>
            <?php 
              endwhile; 
            else:
            ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d;">No hi ha cap reserva per aquest dia.</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div style="text-align: right; margin-top: 20px;">
        <form action="panell_cites.php" method="POST" style="margin: 0; display: inline-block;" onsubmit="return confirm('N\'estàs segur de voler cancel·lar totes les cites del passat?');">
          <button type="submit" name="cancelar_totes_cites" class="btn-admin-remove-worker">Eliminar totes les cites del passat</button>
        </form>
      </div>
      <?php 
// Si la variable no está vacía, la mostramos
if (!empty($mensaje_cites)) {
    echo $mensaje_cites; 
}
?>
    </section>

  </main>
  <footer class="footer-main">
  <div class="footer-links-container">
    <div class="footer-col">
      <h4>Navegació</h4>
      <ul>
        <li><a href="index.php">Inici</a></li>
        <li><a href="qui-som.php">Qui Som</a></li>
        <li><a href="serveis.php">Serveis</a></li>
        <li><a href="equip.php">Equip</a></li>
        <li><a href="productes.php">Productes</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Horaris</h4>
      <ul class="horaris-list">
      <?php if ($resultat_footer && mysqli_num_rows($resultat_footer) > 0) {
    while ($row = mysqli_fetch_assoc($resultat_footer)) {
        $nom_dia = $noms_dies[(int)$row['dia_setmana']];
        if ($row['es_tancat']) {
            echo "<li>{$nom_dia}: Tancat</li>";
        } else {
            $obert = date('H:i', strtotime($row['hora_obertura']));
            $tancat = date('H:i', strtotime($row['hora_tancament']));
            echo "<li>{$nom_dia}: {$obert} - {$tancat}</li>";
        }
    }
} else {
    echo "<li>Dilluns - Divendres: 09:00 - 20:00</li>";
    echo "<li>Dissabtes: 09:00 - 14:00</li>";
    echo "<li>Diumenges: Tancat</li>";
} ?>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Troba'ns</h4>
      <ul>
        <li>📍 Carrer de la Barberia 123, Badalona</li>
        <li>📞 930 123 456</li>
        <li>✉️ contacte@brianbarber.cat</li>
      </ul>
      <div class="social-icons">
        <li class="social-icons">
          <a href="https://www.x.com" aria-label="X (Twitter)" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" /></svg>
          </a>
          <a href="https://www.instagram.com" aria-label="Instagram" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>
          </a>
          <a href="https://www.facebook.com" aria-label="Facebook" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M279.1 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.4 0 225.4 0c-73.22 0-121.1 44.38-121.1 124.7v70.62H22.89V288h81.39v224h100.2V288z"/></svg>
          </a>
        </li>
      </div>
    </div>
    <div class="footer-col">
      <h4>Legal</h4>
      <ul>
        <li><a href="politiques-usuari.php">Avís Legal i Polítiques</a></li>
        <li><a href="politiques-privacitat.php">Privacitat</a></li>
        <li><a href="politiques-compra.php">Compra</a></li>
      </ul>
    </div>
  </div>
  <div class="copyright">
    <p>&copy; <?php echo date("Y"); ?> Brian Barber. Tots els drets reservats.</p>
  </div>
  <div class="footer-images-container">
    <img src="images/dinahosting.png" alt="Dinahosting">
    <img src="images/cultural.png" alt="Colegi Cultural Badalona">
    <img src="images/accent_obert.png" alt="Accent Obert">
  </div>
</footer>
</body>

</html>