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

// Limitem a 4 serveis per a la portada
$sql_serveis = "SELECT * FROM servicios ORDER BY FIELD(categoria, 'Bàsic', 'Combinat', 'Detalls', 'Premium', categoria), precio ASC LIMIT 4";
$res_serveis = mysqli_query($conn, $sql_serveis);

// Limitem a 4 treballadors per a la portada per disseny
$sql_equip = "
    SELECT u.nombre, e.especialitat_desc 
    FROM empleados e 
    JOIN usuarios u ON e.usuarios_id_usuario = u.id_usuario 
    WHERE u.es_trabajador = 1
    LIMIT 4
";
$res_equip = mysqli_query($conn, $sql_equip);

// Limitem a 3 ressenyes amb millor puntuació per a la portada
$sql_top_resenas = "SELECT nombre, puntuacion, comentario, trabajador, fecha FROM reseñas ORDER BY puntuacion DESC, fecha DESC LIMIT 3";
$res_top_resenas = @mysqli_query($conn, $sql_top_resenas);
?>

<!DOCTYPE html>
<html lang="ca">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inici | BrianBarber</title>
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

  <main class="main-content-index">
    <div class="main-layout-wrapper">
      <div class="left-column">
        <div class="content-box box-benvinguts">
          <h2>Benvinguts a Brian Barber</h2>
          <p>El teu espai d'estil i tradició al cor de Badalona. Transformem la teva imatge amb tècniques clàssiques i les últimes tendències en talls masculins.</p>
        </div>

        <div class="content-box box-passio">
          <h2>Passió per la barberia</h2>
          <p>Som barbers amb més de 10 anys d'experiència. A la nostra barberia, no només busquem un bon tall, sinó que gaudeixis d'una experiència de relax i bon tracte. Som especialistes en degradats i cura de la barba.</p>
          <a href="qui-som.php" class="styled-link">Més informació</a>
        </div>

        <div class="content-box box-reserva">
          <h2>Reserva la teva cita online</h2>
          <p>Accedeix al nostre portal de reserves online de forma ràpida i senzilla. Tria el teu servei, barber i horari ideal. El nostre sistema et confirmarà la cita a l'instant, sense esperes.</p>
          <a href="reserves.php" class="btn btn-primary">Anar al Portal de Reserves</a>
        </div>
      </div>

      <div class="right-column">
        <img src="images/barberia.jpg" alt="Interior de la Barberia" class="side-photo">
      </div>
    </div>
    
    <!-- SERVEIS DESTACATS -->
    <section class="box-serveis-contenidor box-serveis-home">
      <h2>Els nostres serveis estrella</h2>
      <p class="subtitle-center">Descobreix els tractaments més sol·licitats a Brian Barber.</p>
      
      <div class="serveis-grid">
        <?php if(mysqli_num_rows($res_serveis) > 0): ?>
          <?php while($servei = mysqli_fetch_assoc($res_serveis)): ?>
          <div class="servei-card">
            <span class="servei-categoria"><?php echo htmlspecialchars($servei['categoria'] ?? 'Bàsic'); ?></span>
            <h3><?php echo htmlspecialchars($servei['nombre_servicio']); ?></h3>
            <p><?php echo htmlspecialchars(!empty($servei['descripcion']) ? $servei['descripcion'] : 'Millora el teu estil amb el nostre servei professional i detallista.'); ?></p>
            <div class="servei-preu-info">
              <span class="opcio-tisora-dark"><?php echo number_format($servei['precio'], 2, ',', '.'); ?> €</span>
            </div>
          </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-empty-data">Els nostres serveis s'estan actualitzant.</p>
        <?php endif; ?>
      </div>

      <div class="action-container-center">
        <a href="serveis.php" class="btn-primary-styled">Veure tota la llista de serveis</a>
      </div>
    </section>

    <!-- EL NOSTRE EQUIP -->
    <section class="box-equip-contenidor box-equip-home">
      <h2>Coneix el nostre equip</h2>
      <p class="subtitle-center">Coneix als professionals darrere de cada tall perfecte. Passió, experiència i el millor tracte per a tu.</p>
      
      <div class="equip-grid equip-grid-flex">
        <?php if(mysqli_num_rows($res_equip) > 0): ?>
          <?php while($empleat = mysqli_fetch_assoc($res_equip)): 
              $img_name = strtolower(str_replace(' ', '', trim($empleat['nombre']))) . '.png';
              $img_path = 'images/' . $img_name;
              if (!file_exists(__DIR__ . '/' . $img_path)) {
                  $img_path = 'images/default_barber.png';
              }
          ?>
          <div class="barber-card barber-card-flex">
            <div class="barber-photo-wrapper">
              <img src="<?php echo $img_path; ?>" alt="<?php echo htmlspecialchars($empleat['nombre']); ?>" class="barber-photo">
            </div>
            <h3><?php echo htmlspecialchars($empleat['nombre']); ?></h3>
            <p><?php echo htmlspecialchars(!empty($empleat['especialitat_desc']) ? $empleat['especialitat_desc'] : "Descobreix els detalls sobre mi i com et puc afavorir la imatge."); ?></p>
          </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-empty-data">Treballem per portar-te als millors professionals aviat.</p>
        <?php endif; ?>
      </div>

      <div class="link-action-center">
        <a href="equip.php" class="styled-link-bold">Aprofundeix al nostre equip</a>
      </div>
    </section>

    <!-- RESSENYES DESTACADES -->
    <section class="box-resenyas-contenidor box-resenyas-home">
      <h2>El que diuen de nosaltres</h2>
      <p class="subtitle-center">Llegeix les opinions dels nostres clients més satisfets.</p>
      
      <div class="resenyas-grid">
        <?php if($res_top_resenas && mysqli_num_rows($res_top_resenas) > 0): ?>
          <?php while($resenya = mysqli_fetch_assoc($res_top_resenas)): ?>
          <div class="resenya-card">
            <div class="resenya-header">
              <strong class="resenya-nom"><?php echo htmlspecialchars($resenya['nombre']); ?></strong>
              <span class="resenya-estrelles" aria-label="<?php echo $resenya['puntuacion']; ?> estrelles">
                <?php echo str_repeat('★', $resenya['puntuacion']) . str_repeat('☆', 5 - $resenya['puntuacion']); ?>
              </span>
            </div>
            <p class="resenya-barber"><strong>Atès per:</strong> <?php echo htmlspecialchars($resenya['trabajador']); ?></p>
            <p class="resenya-comentari">"<?php echo nl2br(htmlspecialchars($resenya['comentario'])); ?>"</p>
          </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-empty-data">Encara no hi ha ressenyes destacades.</p>
        <?php endif; ?>
      </div>

      <div class="link-action-center">
        <a href="resena.php" class="styled-link-bold">Deixa la teva opinió</a>
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