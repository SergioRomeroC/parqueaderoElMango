<?php

include 'db/conexion.php';
requiere_login();

$id = (int)($_GET['id'] ?? 0);

$st = mysqli_prepare($conn, "SELECT * FROM vehiculos WHERE id=?");
mysqli_stmt_bind_param($st, "i", $id);
mysqli_stmt_execute($st);
$v = mysqli_fetch_assoc(mysqli_stmt_get_result($st));

if(!$v){
    mensaje_flash('error', "Vehículo no encontrado");
    header("Location: listar.php");
    exit;
}

$dentro = empty($v['hora_salida']);
$entradaTs = strtotime($v['hora_entrada']);
$finTs = $dentro ? time() : strtotime($v['hora_salida']);
$minutos = minutos_cobrables($entradaTs, $finTs);

$titulo = $dentro ? "Ticket de entrada" : "Ticket de salida";
$subtitulo = $v['placa'];
$activo = "listar";

include 'inc/header.php';
?>

<div class="ticket">

    <div class="cab">
        <img src="img/logo.jpg" alt="Logo">
        <h2>🥭 PARQUEADERO EL MANGO</h2>
        <div>Festival del Mango · El Triunfo</div>
        <div><b><?php echo $dentro ? 'TICKET DE ENTRADA' : 'TICKET DE SALIDA'; ?></b></div>
    </div>

    <hr>

    <span class="placa-grande"><?php echo e($v['placa']); ?></span>

    <div class="linea"><span>Ticket N°</span><b><?php echo str_pad((int)$v['id'], 6, '0', STR_PAD_LEFT); ?></b></div>
    <div class="linea"><span>Tipo</span><b><?php echo e($v['tipo']); ?></b></div>
    <div class="linea"><span>Propietario</span><b><?php echo e($v['propietario']); ?></b></div>
    <div class="linea"><span>Entrada</span><b><?php echo e(date('d/m/Y H:i', $entradaTs)); ?></b></div>

    <?php if(!$dentro){ ?>
        <div class="linea"><span>Salida</span><b><?php echo e(date('d/m/Y H:i', $finTs)); ?></b></div>
        <div class="linea"><span>Tiempo</span><b><?php echo e(formato_duracion($minutos)); ?></b></div>
    <?php } ?>

    <div class="linea">
        <span>Tarifa</span>
        <b><?php echo etiqueta_tarifa($v['tarifa_tipo']); ?> · <?php echo moneda($v['tarifa_valor']); ?></b>
    </div>

    <hr>

    <?php if($dentro){ ?>
        <div class="pie">
            Conserve este ticket.<br>
            El valor se calcula al momento de la salida.
        </div>
    <?php }else{ ?>
        <div class="linea total"><span>TOTAL PAGADO</span><span><?php echo moneda($v['valor_pagado']); ?></span></div>
        <hr>
        <div class="pie">¡Gracias por utilizar<br>Parqueadero El Mango! 🥭</div>
    <?php } ?>

    <div class="no-imprimir">
        <button type="button" class="principal" onclick="window.print()">🖨 Imprimir ticket</button>
        <a class="btn" href="listar.php" style="margin-top:10px;background:var(--bg-2);color:var(--texto);box-shadow:none;border:1px solid var(--borde)">⬅ Volver al listado</a>
    </div>

</div>

<?php include 'inc/footer.php'; ?>
