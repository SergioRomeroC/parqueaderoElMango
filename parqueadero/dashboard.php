<?php

include 'db/conexion.php';
requiere_login();

$total = (int)escalar($conn, "SELECT COUNT(*) FROM vehiculos");
$activos = (int)escalar($conn, "SELECT COUNT(*) FROM vehiculos WHERE hora_salida IS NULL");
$disponibles = max(0, CAPACIDAD - $activos);
$porcentaje = min(100, round($activos / CAPACIDAD * 100));

$recaudoHoy = (float)escalar($conn,
    "SELECT COALESCE(SUM(valor_pagado),0) FROM vehiculos WHERE DATE(hora_salida)=CURDATE()");

$recaudoCaja = (float)escalar($conn,
    "SELECT COALESCE(SUM(valor_pagado),0) FROM vehiculos WHERE cerrado=0 AND hora_salida IS NOT NULL");

$recaudoHistorico = (float)escalar($conn,
    "SELECT COALESCE(SUM(valor_pagado),0) FROM vehiculos");

$entradasHoy = (int)escalar($conn,
    "SELECT COUNT(*) FROM vehiculos WHERE DATE(hora_entrada)=CURDATE()");

$carros = (int)escalar($conn, "SELECT COUNT(*) FROM vehiculos WHERE tipo='Carro' AND hora_salida IS NULL");
$motos  = (int)escalar($conn, "SELECT COUNT(*) FROM vehiculos WHERE tipo='Moto' AND hora_salida IS NULL");
$bicis  = (int)escalar($conn, "SELECT COUNT(*) FROM vehiculos WHERE tipo='Bicicleta' AND hora_salida IS NULL");

// Recaudo de los últimos 7 días
$dias = [];
$valores = [];
for($i = 6; $i >= 0; $i--){
    $dia = date('Y-m-d', strtotime("-$i day"));
    $dias[] = date('d/m', strtotime($dia));
    $valores[] = (float)escalar($conn,
        "SELECT COALESCE(SUM(valor_pagado),0) FROM vehiculos WHERE DATE(hora_salida)=?",
        "s", [$dia]);
}

$titulo = "Dashboard";
$subtitulo = "Panel de control";
$activo = "dashboard";
$conGraficos = true;

include 'inc/header.php';
?>

<?php if($disponibles <= 0){ ?>
    <div class="alerta">⚠ PARQUEADERO LLENO</div>
<?php }elseif($disponibles <= AVISO_CUPOS){ ?>
    <div class="alerta aviso">Quedan pocos cupos: <?php echo $disponibles; ?></div>
<?php } ?>

<div class="card acento">
    <h2>🅿 Ocupación actual: <?php echo $activos; ?> / <?php echo CAPACIDAD; ?></h2>
    <div class="ocupacion">
        <div style="width:<?php echo max($porcentaje, 8); ?>%"><?php echo $porcentaje; ?>%</div>
    </div>
</div>

<div class="stats">

    <div class="card-stat">
        <h3>🅿 Dentro ahora</h3>
        <p><?php echo $activos; ?></p>
    </div>

    <div class="card-stat">
        <h3>✅ Disponibles</h3>
        <p><?php echo $disponibles; ?></p>
    </div>

    <div class="card-stat">
        <h3>🚗 Entradas de hoy</h3>
        <p><?php echo $entradasHoy; ?></p>
    </div>

    <div class="card-stat">
        <h3>💰 Recaudo de hoy</h3>
        <p><?php echo moneda($recaudoHoy); ?></p>
    </div>

    <div class="card-stat">
        <h3>🧾 Caja pendiente</h3>
        <p><?php echo moneda($recaudoCaja); ?></p>
        <small>desde el último cierre</small>
    </div>

    <div class="card-stat">
        <h3>🏆 Recaudo histórico</h3>
        <p><?php echo moneda($recaudoHistorico); ?></p>
        <small><?php echo $total; ?> registros</small>
    </div>

    <div class="card-stat">
        <h3>🚙 Carros dentro</h3>
        <p><?php echo $carros; ?></p>
    </div>

    <div class="card-stat">
        <h3>🏍 Motos dentro</h3>
        <p><?php echo $motos; ?></p>
    </div>

    <div class="card-stat">
        <h3>🚲 Bicicletas dentro</h3>
        <p><?php echo $bicis; ?></p>
    </div>

</div>

<div class="graficos">

    <div class="grafico">
        <h2>Recaudo últimos 7 días</h2>
        <canvas id="graficoBarras"></canvas>
    </div>

    <div class="grafico">
        <h2>Vehículos dentro por tipo</h2>
        <canvas id="graficoCircular"></canvas>
    </div>

</div>

<div class="acciones">

    <form method="POST" action="reiniciar_recaudo.php" class="en-linea" onsubmit="return confirmarCierre()">
        <?php echo campo_csrf(); ?>
        <button type="submit" class="btn-reiniciar">💰 Cerrar caja</button>
    </form>

    <a class="btn-volver" href="reporte.php">📅 Reporte diario</a>
    <a class="btn-volver" href="listar.php?estado=activos">🅿 Ver vehículos dentro</a>

</div>

<script>
let graficoBarras = null;
let graficoCircular = null;

function colorCss(nombre){
    return getComputedStyle(document.documentElement).getPropertyValue(nombre).trim();
}

function dibujarGraficos(){

    const texto = colorCss('--texto');
    const borde = colorCss('--borde');

    Chart.defaults.color = texto;
    Chart.defaults.font.family = "'Poppins', sans-serif";

    if(graficoBarras){ graficoBarras.destroy(); }
    if(graficoCircular){ graficoCircular.destroy(); }

    graficoBarras = new Chart(document.getElementById('graficoBarras'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dias); ?>,
            datasets: [{
                label: 'Recaudo ($)',
                data: <?php echo json_encode($valores); ?>,
                backgroundColor: '#ff8a00',
                borderColor: '#ffd400',
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: borde } },
                y: { beginAtZero: true, grid: { color: borde } }
            }
        }
    });

    graficoCircular = new Chart(document.getElementById('graficoCircular'), {
        type: 'doughnut',
        data: {
            labels: ['Carros', 'Motos', 'Bicicletas'],
            datasets: [{
                data: [<?php echo $carros; ?>, <?php echo $motos; ?>, <?php echo $bicis; ?>],
                backgroundColor: ['#ff4b1f', '#ff8a00', '#2fbf3a'],
                borderColor: colorCss('--card'),
                borderWidth: 3
            }]
        },
        options: {
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

window.recargarGraficos = dibujarGraficos;
document.addEventListener('DOMContentLoaded', dibujarGraficos);
</script>

<?php include 'inc/footer.php'; ?>
