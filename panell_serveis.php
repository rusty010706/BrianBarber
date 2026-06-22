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

$stock = 0;
$mensaje = '';

// Crear taula productes si no existeix per evitar errors i preparar-se
$sql_create_prod = "CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    imagen VARCHAR(255) DEFAULT ''
)";
mysqli_query($conn, $sql_create_prod);

// Actualitzar la taula de serveis per permetre contingut dinàmic (ignorant si ja existeixen)
@mysqli_query($conn, "ALTER TABLE servicios ADD COLUMN categoria VARCHAR(50) DEFAULT 'Bàsic'");
@mysqli_query($conn, "ALTER TABLE servicios ADD COLUMN descripcion TEXT");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion == 'add_servicio') {
        try {
            $nombre = $_POST['nombre_servicio'];
            $precio = (float)$_POST['precio'];
            $duracion = (int)$_POST['duracion'];
            $categoria = isset($_POST['categoria']) ? $_POST['categoria'] : 'Bàsic';
            $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
            
            $sql = "INSERT INTO servicios (nombre_servicio, precio, duracion, categoria, descripcion) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) throw new Exception(mysqli_error($conn));
            mysqli_stmt_bind_param($stmt, "sdiss", $nombre, $precio, $duracion, $categoria, $descripcion);
            mysqli_stmt_execute($stmt);
            $mensaje = "<p class='admin-msg-success'>Servei afegit correctament.</p>";
        } catch (\Throwable $e) {
            $mensaje = "<p class='admin-msg-error'>Error afegint servei: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    elseif ($accion == 'delete_servicio') {
        try {
            $nombre = $_POST['nombre_servicio'];
            $sql = "DELETE FROM servicios WHERE nombre_servicio = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) throw new Exception(mysqli_error($conn));
            mysqli_stmt_bind_param($stmt, "s", $nombre);
            mysqli_stmt_execute($stmt);
            $mensaje = "<p class='admin-msg-success'>Servei esborrat correctament.</p>";
        } catch(\Throwable $e) {
            $mensaje = "<p class='admin-msg-error'>Error esborrant servei: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // --- PRODUCTES ---
    elseif ($accion == 'add_producto') {
        try {
            $nombre = $_POST['nombre'];
            $precio = (float)$_POST['precio'];
            $descripcion = $_POST['descripcion'];
            
            $imagen = 'images/pomada_cabells.png';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $directorio_destino = 'images/';
                if (!is_dir($directorio_destino)) {
                    mkdir($directorio_destino, 0777, true);
                }
                $nombre_archivo = time() . '_' . basename($_FILES['imagen']['name']);
                $ruta_subida = $directorio_destino . $nombre_archivo;
                
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_subida)) {
                    $imagen = $ruta_subida;
                } else {
                    throw new Exception("Error al pujar l'arxiu d'imatge.");
                }
            }
            
            $sql = "INSERT INTO productos (nombre_producto, descripcion, precio, stock, imagen) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) throw new Exception(mysqli_error($conn));
            mysqli_stmt_bind_param($stmt, "ssdis", $nombre, $descripcion, $precio, $stock, $imagen);
            mysqli_stmt_execute($stmt);
            $mensaje = "<p class='admin-msg-success'>Producte afegit correctament.</p>";
        } catch (\Throwable $e) {
            $mensaje = "<p class='admin-msg-error'>Error afegint producte: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    elseif ($accion == 'delete_producto') {
        try {
            $id_producto = (int)$_POST['id_producto'];
            $sql = "DELETE FROM productos WHERE id_producto = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) throw new Exception(mysqli_error($conn));
            mysqli_stmt_bind_param($stmt, "i", $id_producto);
            mysqli_stmt_execute($stmt);
            $mensaje = "<p class='admin-msg-success'>Producte esborrat correctament.</p>";
        } catch (\Throwable $e) {
            $mensaje = "<p class='admin-msg-error'>Error esborrant producte: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

// Llistar
$sql_serveis = "SELECT * FROM servicios ORDER BY precio ASC";
$res_serveis = mysqli_query($conn, $sql_serveis);

$sql_productes = "SELECT * FROM productos ORDER BY precio ASC";
$res_productes = mysqli_query($conn, $sql_productes);

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
        <li><a href="panell_cites.php">Gestió de Cites</a></li>
        <li><a href="panell_usuaris.php">Gestió d'Usuaris</a></li>
        <li><a href="panell_serveis.php" class="active">Serveis i Preus</a></li>
      </ul>
    </aside>

    <section class="admin-content">
      <h2>Serveis i Productes</h2>
      <p>Gestiona aquí el catàleg de la barberia. Pots afegir o eliminar els serveis que es poden reservar i els productes a la venda de la botiga.</p>

      <?php if (!empty($mensaje)) echo $mensaje; ?>

      <!-- SECCIÓ SERVEIS -->
      <div class="admin-section">
        <h3 class="admin-section-title">Llista de Serveis (Talls)</h3>
        
        <table class="admin-table">
          <thead>
            <tr>
              <th>Nom del Servei</th>
              <th>Descripció i Categoria</th>
              <th>Preu</th>
              <th class="center">Accions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($s = mysqli_fetch_assoc($res_serveis)): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($s['nombre_servicio']); ?></strong></td>
              <td>
                  <span class="servei-categoria-badge"><?php echo htmlspecialchars($s['categoria'] ?? 'Bàsic'); ?></span><br>
                  <small><?php echo htmlspecialchars(substr($s['descripcion'] ?? 'Sense descripció establerta', 0, 45)); ?>...</small>
              </td>
              <td class="admin-table-nowrap"><?php echo number_format($s['precio'], 2, ',', '.'); ?> €</td>
              <td class="center">
                <form action="panell_serveis.php" method="POST" style="margin: 0; display: inline;">
                    <input type="hidden" name="accion" value="delete_servicio">
                    <input type="hidden" name="nombre_servicio" value="<?php echo htmlspecialchars($s['nombre_servicio']); ?>">
                    <button type="submit" class="btn-admin-delete">Esborrar</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        
        <div class="admin-form-box">
          <h4>Afegir Nou Servei</h4>
          <form action="" method="POST" class="admin-form-col">
            <input type="hidden" name="accion" value="add_servicio">
            <div class="admin-form-row-gap">
              <input type="text" name="nombre_servicio" placeholder="Nom (ex: Tall clàssic)" class="admin-input admin-input-flex" required>
              <select name="categoria" class="admin-input min-w-200" required>
                <option value="Bàsic">Bàsic</option>
                <option value="Combinat">Combinat</option>
                <option value="Detalls">Detalls</option>
                <option value="Premium">Premium</option>
              </select>
              <input type="number" step="0.5" name="precio" placeholder="Preu (ex: 15.00)" class="admin-input admin-input-small" required>
              <input type="number" step="15" name="duracion" placeholder="Duració (ex: 15 minuts)" class="admin-input admin-input-small" required>
            </div>
            <input type="text" name="descripcion" placeholder="Descripció breu del servei" class="admin-input" required>
            <button type="submit" class="btn-admin-add btn-admin-add-styled">+ Afegir Servei</button>
          </form>
        </div>
      </div>

      <!-- SECCIÓ PRODUCTES -->
      <div class="admin-section">
        <h3 class="admin-section-title">Llista de Productes (Botiga)</h3>
        
        <table class="admin-table">
          <thead>
            <tr>
              <th class="col-img">Img</th>
              <th>Nom</th>
              <th>Descripció</th>
              <th>Preu</th>
              <th class="center">Accions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($p = mysqli_fetch_assoc($res_productes)): ?>
            <tr>
              <?php 
                $imgAdminPath = $p['imagen'];
                if (strpos($imgAdminPath, 'images/') !== 0) {
                    $imgAdminPath = 'images/' . $imgAdminPath;
                }
              ?>
              <td><img src="<?php echo htmlspecialchars($imgAdminPath); ?>" alt="img" class="admin-table-img"></td>
              <td><strong><?php echo htmlspecialchars($p['nombre_producto']); ?></strong></td>
              <td><span class="admin-table-desc"><?php echo htmlspecialchars(substr($p['descripcion'], 0, 50)); ?>...</span></td>
              <td class="text-nowrap"><?php echo number_format($p['precio'], 2, ',', '.'); ?> €</td>
              <td class="center">
                <form action="" method="POST" style="margin: 0; display: inline;">
                    <input type="hidden" name="accion" value="delete_producto">
                    <input type="hidden" name="id_producto" value="<?php echo $p['id_producto']; ?>">
                    <button type="submit" class="btn-admin-delete">Esborrar</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>

        <div class="admin-form-box">
            <h4>Afegir Nou Producte</h4>
            <form action="panell_serveis.php" method="POST" class="admin-form-col" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="add_producto">
                
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="nombre" placeholder="Nom del Producte" class="admin-input admin-input-flex" required>
                    <input type="number" step="0.5" name="precio" placeholder="Preu (ex: 20.00)" class="admin-input admin-input-small" required>
                </div>
                
                <input type="file" name="imagen" accept="image/*" class="admin-input" style="padding-top: 8px;">
                
                <textarea name="descripcion" placeholder="Descripció del producte (ex: Cera d'alta fixació per a pentinats moderns...)" class="admin-input admin-textarea" required></textarea>
                
                <button type="submit" class="btn-admin-add align-self-start">+ Afegir Producte</button>
            </form>
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