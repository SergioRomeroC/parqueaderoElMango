<?php

include 'db/conexion.php';
requiere_login();

// Fecha elegida (por defecto hoy)
$fecha = $_GET['fecha'] ?? date('Y-m-d');
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !strtotime($fecha)){
    $fecha = date('Y-m-d');
}

// Vehículos que entraron o salieron ese día
$st = mysqli_prepare($conn,
    "SELECT * FROM vehiculos
     WHERE DATE(hora_entrada)=? OR DATE(hora_salida)=?
     ORDER BY id DESC");
mysqli_stmt_bind_param($st, "ss", $fecha, $fecha);
mysqli_stmt_execute($st);
$resultado = mysqli_stmt_get_result($st);

$filas = [];
$recaudo = 0;
$porTipo = ['Carro' => [0, 0], 'Moto' => [0, 0], 'Bicicleta' => [0, 0]];
$entradas = 0;
$salidas = 0;

while($f = mysqli_fetch_assoc($resultado)){

    $filas[] = $f;

    if(substr($f['hora_entrada'], 0, 10) === $fecha){
        $entradas++;
    }

    // El dinero se cuenta el día en que el vehículo SALE y paga
    if(!empty($f['hora_salida']) && substr($f['hora_salida'], 0, 10) === $fecha){
        $salidas++;
        $recaudo += (float)$f['valor_pagado'];
        if(isset($porTipo[$f['tipo']])){
            $porTipo[$f['tipo']][0]++;
            $porTipo[$f['tipo']][1] += (float)$f['valor_pagado'];
        }
    }
}

$titulo = "Reporte diario";
$subtitulo = date('d/m/Y', strtotime($fecha));
$activo = "reporte";

include 'inc/header.php';
?>

<form method="GET" class="herramientas no-imprimir">
    <label for="fecha" style="margin:0">📅 Fecha:</label>
    <input type="date" id="fecha" name="fecha" value="<?php echo e($fecha); ?>" max="<?php echo date('Y-m-d'); ?>"
           style="width:auto" onchange="this.form.submit()">
    <a class="btn-volver" href="reporte.php">Hoy</a>
    <button type="button" class="btn-ticket" onclick="window.print()">🖨 Imprimir reporte</button>
</form>

<div class="stats">

    <div class="card-stat">
        <h3>💰 Recaudo del día</h3>
        <p><?php echo moneda($recaudo); ?></p>
    </div>

    <div class="card-stat">
        <h3>⬇ Entradas</h3>
        <p><?php echo $entradas; ?></p>
    </div>

    <div class="card-stat">
        <h3>⬆ Salidas</h3>
        <p><?php echo $salidas; ?></p>
    </div>

    <?php foreach($porTipo as $tipo => $d){ ?>
    <div class="card-stat">
        <h3><?php echo icono_tipo($tipo) . ' ' . e($tipo); ?></h3>
        <p><?php echo moneda($d[1]); ?></p>
        <small><?php echo $d[0]; ?> salidas</small>
    </div>
    <?php } ?>

</div>

<div class="tabla-wrap">

<table>
<thead>
<tr>
    <th>ID</th>
    <th>Placa</th>
    <th>Tipo</th>
    <th>Propietario</th>
    <th>Entrada</th>
    <th>Salida</th>
    <th>Tarifa</th>
    <th>Valor</th>
</tr>
</thead>
<tbody>
<?php if(!$filas){ ?>
<tr><td colspan="8" class="suave" style="text-align:center;padding:24px">No hay movimientos en esta fecha.</td></tr>
<?php } ?>

<?php foreach($filas as $f){ ?>
<tr>
    <td><?php echo (int)$f['id']; ?></td>
    <td><span class="placa"><?php echo e($f['placa']); ?></span></td>
    <td><?php echo icono_tipo($f['tipo']) . ' ' . e($f['tipo']); ?></td>
    <td><?php echo e($f['propietario']); ?></td>
    <td><?php echo e(date('d/m/Y H:i', strtotime($f['hora_entrada']))); ?></td>
    <td>
        <?php echo empty($f['hora_salida'])
            ? '<span class="badge dentro">● Dentro</span>'
            : e(date('d/m/Y H:i', strtotime($f['hora_salida']))); ?>
    </td>
    <td><?php echo etiqueta_tarifa($f['tarifa_tipo']); ?></td>
    <td><b><?php echo moneda($f['valor_pagado']); ?></b></td>
</tr>
<?php } ?>
</tbody>
</table>

</div>

<div class="card acento" style="margin-top:22px;text-align:center">
    <h2 style="justify-content:center">💰 Recaudo total del día</h2>
    <p class="total-grande"><?php echo moneda($recaudo); ?></p>
</div>

<?php include 'inc/footer.php'; ?>
