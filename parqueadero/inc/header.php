<?php
/*
 * Encabezado común.
 * Uso:  $titulo = "..."; $subtitulo = "..."; $activo = "listar"; $compacto = true|false;
 *       include 'inc/header.php';
 */
$titulo    = $titulo    ?? NOMBRE_NEGOCIO;
$subtitulo = $subtitulo ?? '';
$activo    = $activo    ?? '';
$compacto  = $compacto  ?? true;
$conGraficos = $conGraficos ?? false;
$enlaces = [
    'index'     => ['index.php',     '🏠 Inicio'],
    'registrar' => ['registrar.php', '🚗 Registrar'],
    'listar'    => ['listar.php',    '📋 Vehículos'],
    'dashboard' => ['dashboard.php', '📊 Dashboard'],
    'reporte'   => ['reporte.php',   '📅 Reporte'],
];
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($titulo); ?> · <?php echo e(NOMBRE_NEGOCIO); ?></title>
<link rel="icon" href="img/logo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Russo+One&display=swap" rel="stylesheet">
<script>
/* Aplica el tema guardado antes de pintar, para evitar parpadeos */
try{
    var t = localStorage.getItem('tema_mango');
    document.documentElement.setAttribute('data-theme', t === 'light' ? 'light' : 'dark');
}catch(e){}
</script>
<link rel="stylesheet" href="css/estilos.css">
<?php if($conGraficos){ ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php } ?>
<script src="js/script.js" defer></script>
</head>
<body>

<header class="hero<?php echo $compacto ? ' compacto' : ''; ?>">
    <img class="logo-img" src="img/logo.jpg" alt="Festival del Mango · El Triunfo · Parqueadero de motos">
    <h1><?php echo e($titulo); ?></h1>
    <?php if($subtitulo !== ''){ ?><p><?php echo e($subtitulo); ?></p><?php } ?>
    <div class="reloj" id="reloj"></div>
</header>

<nav class="nav">
    <?php foreach($enlaces as $clave => $dato){ ?>
        <a href="<?php echo $dato[0]; ?>" class="<?php echo $activo === $clave ? 'activo' : ''; ?>"><?php echo $dato[1]; ?></a>
    <?php } ?>
    <button type="button" id="btnTema" onclick="cambiarTema()">☀️ Modo claro</button>
    <a href="logout.php" class="salir">🚪 Salir</a>
</nav>

<main class="contenedor<?php echo !empty($estrecho) ? ' estrecho' : ''; ?>">
<?php
$flash = mensaje_flash();
if($flash){
    echo '<div class="mensaje ' . e($flash[0]) . '">' . e($flash[1]) . '</div>';
}
?>
