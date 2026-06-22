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

// 1. SEGURIDAD VITAL: Si no es empleado (valor 1), lo echamos de aquí.
if (!isset($_SESSION['empleado']) || $_SESSION['empleado'] != 1 ) {
  header('Location: index.php');
  exit();
  if ($_SESSION['rol'] == 'supervisor') {
    header('Location: panell.php');
    exit();
  }
}

require_once 'includes/conexions.php';

// Actualitzar la taula empleats per permetre dinàmics de l'Especialitat
@mysqli_query($conn, "ALTER TABLE empleados ADD COLUMN especialitat_desc TEXT");

$is_supervisor = false;
$id_logged = $_SESSION['id_usuario'] ?? 0;
if ($id_logged > 0) {
    $sql_check_sup = "SELECT rol FROM empleados WHERE usuarios_id_usuario = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check_sup);
    mysqli_stmt_bind_param($stmt_check, "i", $id_logged);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);
    if ($row_check = mysqli_fetch_assoc($res_check)) {
        if ($row_check['rol'] === 'supervisor') {
            $is_supervisor = true;
        }
    }
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_begin_transaction($conn);
    try {
        if (isset($_POST['accion_hacer'])) {
            $id_target = (int)$_POST['accion_hacer'];
            $sql_upd = "UPDATE usuarios SET es_trabajador = 1 WHERE id_usuario = ?";
            $stmt_upd = mysqli_prepare($conn, $sql_upd);
            mysqli_stmt_bind_param($stmt_upd, "i", $id_target);
            mysqli_stmt_execute($stmt_upd);
            
            $sql_ins = "INSERT IGNORE INTO empleados (usuarios_id_usuario, rol) VALUES (?, 'empleado')";
            $stmt_ins = mysqli_prepare($conn, $sql_ins);
            mysqli_stmt_bind_param($stmt_ins, "i", $id_target);
            mysqli_stmt_execute($stmt_ins);

            $mensaje = "<p class='admin-msg-success'>L'usuari ha estat ascendit a treballador correctament.</p>";

        } elseif (isset($_POST['accion_quitar'])) {
            $id_target = (int)$_POST['accion_quitar'];
            $sql_upd = "UPDATE usuarios SET es_trabajador = 0 WHERE id_usuario = ?";
            $stmt_upd = mysqli_prepare($conn, $sql_upd);
            mysqli_stmt_bind_param($stmt_upd, "i", $id_target);
            mysqli_stmt_execute($stmt_upd);
            
            $sql_del = "DELETE FROM empleados WHERE usuarios_id_usuario = ?";
            $stmt_del = mysqli_prepare($conn, $sql_del);
            mysqli_stmt_bind_param($stmt_del, "i", $id_target);
            mysqli_stmt_execute($stmt_del);

            $mensaje = "<p class='admin-msg-success'>S'ha retirat el rol de treballador a l'usuari correctament.</p>";
            
        } elseif (isset($_POST['accion_eliminar']) && $is_supervisor) {
            $id_target = (int)$_POST['accion_eliminar'];
            if ($id_target !== $id_logged) {
                @mysqli_query($conn, "DELETE FROM empleados WHERE usuarios_id_usuario = $id_target");
                @mysqli_query($conn, "DELETE FROM clientes WHERE usuarios_id_usuario = $id_target");
                
                $sql_del_usr = "DELETE FROM usuarios WHERE id_usuario = ?";
                $stmt_del_usr = mysqli_prepare($conn, $sql_del_usr);
                mysqli_stmt_bind_param($stmt_del_usr, "i", $id_target);
                if (mysqli_stmt_execute($stmt_del_usr)) {
                    $mensaje = "<p class='admin-msg-success'>S'ha eliminat l'usuari correctament.</p>";
                } else {
                    $mensaje = "<p class='admin-msg-error'>No s'ha pogut eliminar l'usuari (és possible que tingui reserves associades).</p>";
                }
            } else {
                $mensaje = "<p class='admin-msg-error'>No pots eliminar el teu propi usuari.</p>";
            }

        } elseif (isset($_POST['accion']) && $_POST['accion'] == 'guardar_todo' && isset($_POST['empleados_data'])) {
            $sql_upd = "UPDATE empleados SET rol = ?, num_segsocial = ?, especialitat_desc = ? WHERE usuarios_id_usuario = ?";
            $stmt_upd = mysqli_prepare($conn, $sql_upd);
            foreach ($_POST['empleados_data'] as $id => $data) {
                $nuevo_rol = $data['rol'];
                $nuevo_ssn = !empty($data['ssn']) ? $data['ssn'] : NULL;
                $nueva_esp = !empty($data['especialitat']) ? $data['especialitat'] : NULL;
                $id_int = (int)$id;
                mysqli_stmt_bind_param($stmt_upd, "sssi", $nuevo_rol, $nuevo_ssn, $nueva_esp, $id_int);
                mysqli_stmt_execute($stmt_upd);
            }
            $mensaje = "<p class='admin-msg-success'>Canvis desats correctament per a tots els treballadors.</p>";
        }
        
        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $mensaje = "<p class='admin-msg-error'>Error a l'actualitzar la base de dades: " . $e->getMessage() . "</p>";
    }
}

$filtro_rol = isset($_GET['filtro_rol']) ? $_GET['filtro_rol'] : 'todos';
$filtro_busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

$condicion = "1=1";
if ($filtro_rol === 'clientes') {
    $condicion .= " AND u.es_trabajador = 0";
} elseif ($filtro_rol === 'empleados') {
    $condicion .= " AND u.es_trabajador = 1 AND (e.rol = 'empleado' OR e.rol IS NULL)";
} elseif ($filtro_rol === 'supervisores') {
    $condicion .= " AND u.es_trabajador = 1 AND e.rol = 'supervisor'";
} elseif ($filtro_rol === 'todos_trabajadores') {
    $condicion .= " AND u.es_trabajador = 1";
}

if (!empty($filtro_busqueda)) {
    $busqueda_esc = mysqli_real_escape_string($conn, $filtro_busqueda);
    $condicion .= " AND (u.nombre LIKE '%$busqueda_esc%' OR u.apellidos LIKE '%$busqueda_esc%' OR u.correo LIKE '%$busqueda_esc%')";
}

$sql_usuarios = "
    SELECT u.id_usuario, u.nombre, u.apellidos, u.correo, u.es_trabajador, e.rol, e.num_segsocial, e.especialitat_desc 
    FROM usuarios u
    LEFT JOIN empleados e ON u.id_usuario = e.usuarios_id_usuario
    WHERE $condicion
    ORDER BY u.nombre ASC";
$resultado_usuarios = mysqli_query($conn, $sql_usuarios);

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
        <li><a href="panell_usuaris.php"class="active">Gestió d'Usuaris</a></li>
        <li><a href="panell_serveis.php">Serveis i Preus</a></li>
      </ul>
    </aside>

    <section class="admin-content">
      <h2>Gestió d'Usuaris i Empleats</h2>
      <p>Aquesta secció permet gestionar els rols dels usuaris. Pots convertir usuaris normals en treballadors (empleats) de la barberia per tal que apareguin al sistema de reserves, o retirar-los el rol.</p>
      
      <?php if (!empty($mensaje)) echo $mensaje; ?>

      <form method="GET" action="panell_usuaris.php" class="admin-filter-form" style="display: flex; gap: 15px; margin-bottom: 20px; align-items: center; flex-wrap: wrap; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <div>
            <label for="filtro_rol" style="font-weight: bold; margin-right: 5px;">Rol:</label>
            <select name="filtro_rol" id="filtro_rol" class="admin-select-field" style="width: auto;">
                <option value="todos" <?php echo $filtro_rol == 'todos' ? 'selected' : ''; ?>>Tots</option>
                <option value="clientes" <?php echo $filtro_rol == 'clientes' ? 'selected' : ''; ?>>Només Clients</option>
                <option value="todos_trabajadores" <?php echo $filtro_rol == 'todos_trabajadores' ? 'selected' : ''; ?>>Tots els Treballadors</option>
                <option value="empleados" <?php echo $filtro_rol == 'empleados' ? 'selected' : ''; ?>>Treballadors (Base)</option>
                <option value="supervisores" <?php echo $filtro_rol == 'supervisores' ? 'selected' : ''; ?>>Supervisors</option>
            </select>
        </div>
        <div>
            <label for="buscar" style="font-weight: bold; margin-right: 5px;">Cercar per text:</label>
            <input type="text" name="buscar" id="buscar" value="<?php echo htmlspecialchars($filtro_busqueda); ?>" placeholder="Nom, cognoms o correu..." class="admin-input" style="width: 250px;">
        </div>
        <button type="submit" class="btn-admin-add" style="padding: 10px 15px; margin: 0; min-width: auto;">Aplicar Filtres</button>
        <?php if ($filtro_rol !== 'todos' || !empty($filtro_busqueda)): ?>
            <a href="panell_usuaris.php" style="color: #666; text-decoration: underline; margin-left: auto;">Netejar filtres</a>
        <?php endif; ?>
      </form>

      <?php
        $query_string = '';
        if (isset($_GET['filtro_rol']) || isset($_GET['buscar'])) {
            $query_params = [];
            if (isset($_GET['filtro_rol'])) $query_params['filtro_rol'] = $_GET['filtro_rol'];
            if (isset($_GET['buscar'])) $query_params['buscar'] = $_GET['buscar'];
            if (!empty($query_params)) {
               $query_string = '?' . http_build_query($query_params);
            }
        }
      ?>
      <form action="panell_usuaris.php<?php echo $query_string; ?>" method="POST">
        <div class="table-responsive">
          <table class="table-users">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nom i Cognoms</th>
                <th>Correu</th>
                <th class="center">Rol</th>
                <th class="center">Seguretat Social</th>
                <th>Especialitat / Bio</th>
                <th class="center">Accions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($usuario = mysqli_fetch_assoc($resultado_usuarios)): ?>
              <tr>
                <td><?php echo $usuario['id_usuario']; ?></td>
                <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></td>
                <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                <td class="center">
                  <?php if ($usuario['es_trabajador'] == 1): ?>
                      <select name="empleados_data[<?php echo $usuario['id_usuario']; ?>][rol]" class="admin-select-field">
                          <option value="empleado" <?php echo ($usuario['rol'] == 'empleado' || empty($usuario['rol'])) ? 'selected' : ''; ?>>Treballador</option>
                          <option value="supervisor" <?php echo ($usuario['rol'] == 'supervisor') ? 'selected' : ''; ?>>Supervisor</option>
                      </select>
                  <?php else: ?>
                    <span class="label-client">Client</span>
                  <?php endif; ?>
                </td>
                <td class="center">
                  <?php if ($usuario['es_trabajador'] == 1): ?>
                      <input type="text" name="empleados_data[<?php echo $usuario['id_usuario']; ?>][ssn]" value="<?php echo htmlspecialchars($usuario['num_segsocial'] ?? ''); ?>" placeholder="Num. Seguridad Social" class="admin-input admin-input-full-width">
                  <?php else: ?>
                      <span style="color: #999;">--</span>
                  <?php endif; ?>
                </td>
                <td class="center">
                  <?php if ($usuario['es_trabajador'] == 1): ?>
                      <input type="text" name="empleados_data[<?php echo $usuario['id_usuario']; ?>][especialitat]" value="<?php echo htmlspecialchars($usuario['especialitat_desc'] ?? ''); ?>" placeholder="Especialitat breu" class="admin-input admin-input-full-width">
                  <?php else: ?>
                      <span style="color: #999;">--</span>
                  <?php endif; ?>
                </td>
                <td class="center">
                  <div style="display: flex; gap: 5px; justify-content: center;">
                    <?php if ($usuario['es_trabajador'] == 1): ?>
                        <button type="submit" name="accion_quitar" value="<?php echo $usuario['id_usuario']; ?>" class="btn-admin-remove-worker btn-admin-remove-sm">Treure Treballador</button>
                    <?php else: ?>
                        <button type="submit" name="accion_hacer" value="<?php echo $usuario['id_usuario']; ?>" class="btn-admin-make-worker btn-admin-remove-sm" style="padding: 6px 10px;">Fer Treballador</button>
                    <?php endif; ?>

                    <?php if ($is_supervisor && $usuario['id_usuario'] != $id_logged): ?>
                        <button type="submit" name="accion_eliminar" value="<?php echo $usuario['id_usuario']; ?>" class="btn-admin-remove-worker btn-admin-remove-sm" style="background-color: #d9534f;" onclick="return confirm('Estàs segur d\'eliminar aquest usuari completament?');">Eliminar</button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        
        <div class="admin-submit-container">
            <input type="hidden" name="accion" value="guardar_todo">
            <button type="submit" class="btn-admin-add btn-admin-add-lg">Desar Tots els Canvis</button>
        </div>
      </form>
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