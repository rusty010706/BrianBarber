<?php
session_start();
require 'includes/conexions.php';

if (!isset($_SESSION['usuario_nombre'])) {
  header('Location: accedir.php');
}

$nombre = $_SESSION['usuario_nombre'];

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

// Crear tabla automática si no existe (alojada en resena.php para no tocar otros archivos)
$sql_resenas = "CREATE TABLE IF NOT EXISTS reseñas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    puntuacion INT NOT NULL CHECK (puntuacion >= 1 AND puntuacion <= 5),
    comentario TEXT,
    trabajador VARCHAR(100) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
@mysqli_query($conn, $sql_resenas);

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $puntuacion = (int)$_POST['puntuacion'];
    $comentario = mysqli_real_escape_string($conn, trim($_POST['comentario']));
    $trabajador = mysqli_real_escape_string($conn, trim($_POST['trabajador']));
    
    if ($puntuacion >= 1 && $puntuacion <= 5 && !empty($nombre) && !empty($comentario) && !empty($trabajador)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO reseñas (nombre, puntuacion, comentario, trabajador) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "siss", $nombre, $puntuacion, $comentario, $trabajador);
        if (mysqli_stmt_execute($stmt)) {
          $mensaje = "<p class='admin-msg-success' style='text-align:center;'>Gràcies! S'ha enviat la teva ressenya.</p>";
          header("Refresh: 3; url=index.php");
       } else {
            $mensaje = "<p class='admin-msg-error' style='text-align:center;'>Error al guardar la teva ressenya.</p>";
        }
        mysqli_stmt_close($stmt);
    } else {
        $mensaje = "<p class='admin-msg-error' style='text-align:center;'>Falten dades o la puntuació és incorrecta.</p>";
    }
}

// Obtener lista de trabajadores para el select
$sql_trabajadores = "SELECT u.nombre FROM empleados e JOIN usuarios u ON e.usuarios_id_usuario = u.id_usuario WHERE u.es_trabajador = 1";
$res_trabajadores = @mysqli_query($conn, $sql_trabajadores);
$trabajadores = $res_trabajadores ? mysqli_fetch_all($res_trabajadores, MYSQLI_ASSOC) : [];

// Extraer opiniones enviadas
$resenas_query = @mysqli_query($conn, "SELECT nombre, puntuacion, comentario, trabajador, fecha FROM reseñas ORDER BY fecha DESC LIMIT 20");
$resenas = $resenas_query ? mysqli_fetch_all($resenas_query, MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="ca">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ressenyes | BrianBarber</title>
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

<main class="main-contacte">
  <div class="box-contacte">
    <h2>Què penses de nosaltres?</h2>
    <p>Valorem la teva opinió. Deixa'ns una ressenya d'1 a 5 estrelles!</p>
    
    <?= $mensaje ?>

    <form action="resena.php" method="POST" class="contacte-form">

      <div class="input-group">
        <label for="puntuacion">Puntuació:</label>
        <div class="select-wrapper">
          <select id="puntuacion" name="puntuacion" required>
            <option value="5" selected>⭐⭐⭐⭐⭐ Excel·lent</option>
            <option value="4">⭐⭐⭐⭐ Molt bo</option>
            <option value="3">⭐⭐⭐ Bo</option>
            <option value="2">⭐⭐ Regular</option>
            <option value="1">⭐ Dolent</option>
          </select>
        </div>
      </div>

      <div class="input-group">
        <label for="trabajador">Qui t'ha atès?:</label>
        <div class="select-wrapper">
          <select id="trabajador" name="trabajador" required>
            <option value="" disabled selected>Selecciona el barber</option>
            <?php foreach($trabajadores as $t): ?>
              <option value="<?= htmlspecialchars($t['nombre']) ?>"><?= htmlspecialchars($t['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="input-group">
        <label for="comentario">La teva opinió:</label>
        <textarea id="comentario" name="comentario" rows="4" placeholder="Opina el que vulguis..." required></textarea>
      </div>

      <button type="submit" class="btn-enviar-contacte">Publicar ressenya</button>
    </form>

    <section style="margin-top: 4rem;">
      <h3>Últimes Opinions</h3>
      <?php if(empty($resenas)): ?>
        <p style="color: #777;">Encara no hi ha ressenyes. Sigues el primer!</p>
      <?php else: ?>
        <ul style="list-style:none; padding:0;">
          <?php foreach($resenas as $r): ?>
             <li style="border-bottom: 1px solid #eaeaea; padding: 1.5rem 0; display: flex; flex-direction: column; gap: 0.5rem;">
               <div style="display: flex; justify-content: space-between;">
                 <strong style="color: #111; font-size: 1.1rem;"><?= htmlspecialchars($r['nombre']) ?></strong> 
                 <span style="color:#f39c12; font-size: 1.2rem;" aria-label="<?= $r['puntuacion'] ?> estrelles">
                   <?= str_repeat('★', $r['puntuacion']) . str_repeat('☆', 5 - $r['puntuacion']) ?>
                 </span>
               </div>
               <p style="margin: 0; color: #555; font-size: 0.9rem;"><strong>Atès per:</strong> <?= htmlspecialchars($r['trabajador']) ?></p>
               <p style="margin: 0; color: #555; line-height: 1.5;"><?= nl2br(htmlspecialchars($r['comentario'])) ?></p>
               <small style="color: #999;"><?= date('d-m-Y H:i', strtotime($r['fecha'])) ?></small>
             </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
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

</body>
</html>
