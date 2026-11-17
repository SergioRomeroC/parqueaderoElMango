<?php

include 'db/conexion.php';
requiere_login();

$activos = (int)escalar($conn, "SELECT COUNT(*) FROM vehiculos WHERE hora_salida IS NULL");
$disponibles = max(0, CAPACIDAD - $activos);
$hoy = (float)escalar($conn,
    "SELECT COALESCE(SUM(valor_pagado),0) FROM vehiculos WHERE DATE(hora_salida)=CURDATE()");

$titulo = "Parqueadero El Mango";
$subtitulo = "Sistema Inteligente de Gestión Vehicular";
$activo = "index";
$compacto = false;

include 'inc/header.php';
?>

<div class="stats">

    <div class="card-stat">
        <h3>🅿 Vehículos dentro</h3>
        <p><?php echo $activos; ?></p>
    </div>

    <div class="card-stat">
        <h3>✅ Cupos disponibles</h3>
        <p><?php echo $disponibles; ?></p>
        <small>de <?php echo CAPACIDAD; ?> cupos</small>
    </div>

    <div class="card-stat">
        <h3>💰 Recaudo de hoy</h3>
        <p><?php echo moneda($hoy); ?></p>
    </div>

</div>

<?php if($disponibles <= 0){ ?>
    <div class="alerta">⚠ PARQUEADERO LLENO</div>
<?php }elseif($disponibles <= AVISO_CUPOS){ ?>
    <div class="alerta aviso">Quedan pocos cupos: <?php echo $disponibles; ?></div>
<?php } ?>

<div class="accesos">

    <a class="acceso" href="registrar.php">
        <span class="icono">🏍</span>
        <strong>Registrar entrada</strong>
        <span class="suave">Nuevo vehículo al parqueadero</span>
    </a>

    <a class="acceso" href="listar.php?estado=activos">
        <span class="icono">🅿</span>
        <strong>Vehículos dentro</strong>
        <span class="suave">Registrar salidas y cobrar</span>
    </a>

    <a class="acceso" href="listar.php">
        <span class="icono">📋</span>
        <strong>Historial</strong>
        <span class="suave">Todos los registros</span>
    </a>

    <a class="acceso" href="dashboard.php">
        <span class="icono">📊</span>
        <strong>Dashboard</strong>
        <span class="suave">Estadísticas y gráficas</span>
    </a>

    <a class="acceso" href="reporte.php">
        <span class="icono">📅</span>
        <strong>Reporte diario</strong>
        <span class="suave">Recaudo por fecha</span>
    </a>

</div>

<div class="card acento">
    <h2>👋 Bienvenido al sistema</h2>
    <p class="suave">
        Controle entradas y salidas, cobre por minuto o con tarifa plena,
        imprima tickets, visualice estadísticas y consulte reportes diarios de
        forma sencilla.
    </p>
</div>

<?php include 'inc/footer.php'; ?>
