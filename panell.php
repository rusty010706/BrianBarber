<?php
session_start();

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


if (!isset($_SESSION['empleado']) || $_SESSION['empleado'] != 1) {
  header('Location: index.php');
  exit();
}

require_once 'includes/conexions.php';

$fecha = date("Y-m-d");

$sql_citas = "SELECT * FROM citas WHERE fecha = ?";
$stmt_citas = mysqli_prepare($conn, $sql_citas);
mysqli_stmt_bind_param($stmt_citas, "s", $fecha);
mysqli_stmt_execute($stmt_citas);
$resultado = mysqli_stmt_get_result($stmt_citas);
$num_citas = mysqli_num_rows($resultado);

$sql_clients = "SELECT * FROM usuarios";
$stmt_clients = mysqli_prepare($conn, $sql_clients);
mysqli_stmt_execute($stmt_clients);
$resultado = mysqli_stmt_get_result($stmt_clients);
$num_clients = mysqli_num_rows($resultado);

$mes = date("m");
$any = date("Y");

$sql_ingressos = "SELECT c.tall, COUNT(*) as quantitat, SUM(COALESCE(c.tissores, 0)) as extres, s.precio 
                  FROM citas c
                  LEFT JOIN servicios s ON c.tall = s.nombre_servicio
                  WHERE MONTH(c.fecha) = ? AND YEAR(c.fecha) = ? 
                  GROUP BY c.tall, s.precio";
                  
$stmt_ingressos = mysqli_prepare($conn, $sql_ingressos);
mysqli_stmt_bind_param($stmt_ingressos, "ss", $mes, $any);
mysqli_stmt_execute($stmt_ingressos);
$resultado_ingressos = mysqli_stmt_get_result($stmt_ingressos);

$total_mes = 0;

while ($fila = mysqli_fetch_assoc($resultado_ingressos)) {
    $quantitat = $fila['quantitat'];
    $preu_unitari = $fila['precio'] ? $fila['precio'] : 0;
    $extres = $fila['extres'] ? $fila['extres'] : 0;
    
    // Multiplicació de gent que s'ha fet el servei pel preu, més extres
    $total_mes += ($quantitat * $preu_unitari) + $extres;
}

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
        <li><a href="panell.php" class="active">Resum (Dashboard)</a></li>
        <li><a href="panell_cites.php">Gestió de Cites</a></li>
        <li><a href="panell_usuaris.php">Gestió d'Usuaris</a></li>
        <li><a href="panell_serveis.php">Serveis i Preus</a></li>
      </ul>
    </aside>

    <section class="admin-content">
      <h2>Tauler Principal</h2>
      <p>Aquest és el teu centre de control. Selecciona una opció del menú lateral per començar a gestionar BrianBarber.
      </p>

      <div class="dashboard-cards">
        <div class="dash-card">
          <h3>Cites per Avui</h3>
          <span class="dash-number">
            <?php
              echo $num_citas;
            ?>
          </span>
        </div>

        <div class="dash-card">
          <h3>Total Clients</h3>
          <span class="dash-number">
            <?php
              echo $num_clients;
            ?>
          </span>
        </div>

        <div class="dash-card">
          <h3>Ingressos (Mes)</h3>
          <span class="dash-number">
            <?php
              echo number_format($total_mes, 2, ',', '.') . '€';
            ?>
          </span>
        </div>
      </div>
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