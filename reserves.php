<?php
session_start();
// 1. Incluimos la conexión al principio para que los datos estén listos antes de pintar el HTML
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

if (!isset($_SESSION['usuario_nombre'])) {
  header('Location: accedir.php');
  exit();
}
// 2. Preparamos la consulta de servicios para el desplegable 
$sql_servicios = "SELECT nombre_servicio, precio FROM servicios";
$stmt_servicios = mysqli_prepare($conn, $sql_servicios);
mysqli_stmt_execute($stmt_servicios);
$resultat_servicios = mysqli_stmt_get_result($stmt_servicios);

// 1. La consulta correcta usando JOIN
$sql_trabajadores = "SELECT e.id_empleado, u.nombre, u.apellidos 
                     FROM empleados as e 
                     JOIN usuarios as u ON e.usuarios_id_usuario = u.id_usuario";
$stmt_trabajadores = mysqli_prepare($conn, $sql_trabajadores);
mysqli_stmt_execute($stmt_trabajadores);
$resultat_trabajador = mysqli_stmt_get_result($stmt_trabajadores);


?>

<!DOCTYPE html>
<html lang="ca">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservar Cita | BrianBarber</title>
  <link rel="stylesheet" href="style.css?v=2">
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

  <main class="reserva-container">
    <div class="reserva-card-ample">
      <h2>Reservar cita</h2>
      <p>Selecciona els detalls del teu servei</p>

      <form action="processar_reserva.php" method="post" class="reserva-form">

        <div class="form-row-reserva">
          <div class="form-group flex-1">
            <label for="data_reserva">Data de la cita</label>
            <input type="date" id="data_reserva" name="data_reserva" required>
          </div>

          <div class="form-group flex-1">
            <label for="hora">Hora de la cita</label>
            <input type="time" id="hora" name="hora" required>
          </div>

          <div class="form-group flex-1">
            <label for="tipus_tall">Tipus de tall</label>
            <div class="select-wrapper">
              <select id="tipus_tall" name="tipus_tall" required onchange="actualitzarTotal()">
                <option value="" disabled selected>Tria una opció...</option>
                <?php
                while ($servei = mysqli_fetch_assoc($resultat_servicios)) {
                  $nom = htmlspecialchars($servei['nombre_servicio'], ENT_QUOTES);
                  $preu = $servei['precio'];
                  echo "<option value='$nom' data-preu='$preu'>" . htmlspecialchars($servei['nombre_servicio']) . " - $preu €</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="form-group flex-1">
            <label for="treballador">Treballador a seleccionar</label>
            <div class="select-wrapper">
              <select id="treballador" name="trabajador" required>
                <option value="" disabled selected>Tria un barber...</option>
                  <?php
                  while ($trabajador = mysqli_fetch_assoc($resultat_trabajador)) {
                    $id_barbero = $trabajador['id_empleado'];
                    $nom = $trabajador['nombre'];
                    $cognom = $trabajador['apellidos'];

                    echo "<option value='$id_barbero'>$cognom, $nom</option>";
                  }
                  ?>
                </select>
            </div>
          </div>
        </div>

        <div class="form-group checkbox-extra">
          <label class="custom-checkbox">
            <input type="checkbox" name="extra_tisora" id="extra_tisora" value="2" onchange="actualitzarTotal()">
            <span class="checkmark"></span>
            Afegir tall a tisora (+2€)
          </label>
        </div>
        
        <div class="resum-preu">
          <div class="resum-fila">
            <span>Preu base:</span>
            <span id="preu_base_txt">0,00€</span>
          </div>
          <div class="resum-fila total-destacat">
            <span>Total estimat:</span>
            <span id="total_estimat_txt">0,00€</span>
          </div>
        </div>
        <button type="submit" class="btn-submit btn-reserva">Continuar</button>
      </form>
    </div>
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

  <script>
    function actualitzarTotal() {
      const select = document.getElementById('tipus_tall');
      const checkbox = document.getElementById('extra_tisora');
      const txtBase = document.getElementById('preu_base_txt');
      const txtTotal = document.getElementById('total_estimat_txt');

      let preuBase = 0;

      if (select.selectedIndex > 0) {
        // Obtenemos el precio del atributo data-preu que pusimos en el PHP
        preuBase = parseFloat(select.options[select.selectedIndex].getAttribute('data-preu'));
      }

      let extra = checkbox.checked ? 2 : 0;
      let total = preuBase + extra;

      txtBase.innerText = preuBase.toFixed(2).replace('.', ',') + '€';
      txtTotal.innerText = total.toFixed(2).replace('.', ',') + '€';
    }
  </script>

</body>

</html>