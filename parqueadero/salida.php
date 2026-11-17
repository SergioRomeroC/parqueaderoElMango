<?php

include 'db/conexion.php';
requiere_login();
solo_post();
verificar_csrf();

$id = (int)($_POST['id'] ?? 0);

$st = mysqli_prepare($conn, "SELECT * FROM vehiculos WHERE id=?");
mysqli_stmt_bind_param($st, "i", $id);
mysqli_stmt_execute($st);
$vehiculo = mysqli_fetch_assoc(mysqli_stmt_get_result($st));

if(!$vehiculo){
    mensaje_flash('error', "Vehículo no encontrado");
    header("Location: listar.php");
    exit;
}

if(!empty($vehiculo['hora_salida'])){
    header("Location: ticket.php?id=" . $id);
    exit;
}

$entradaTs = strtotime($vehiculo['hora_entrada']);
$salidaTs = time();

$valor = calcular_valor(
    $vehiculo['tarifa_tipo'],
    $vehiculo['tarifa_valor'],
    $entradaTs,
    $salidaTs
);

$horaSalida = date('Y-m-d H:i:s', $salidaTs);

$up = mysqli_prepare($conn,
    "UPDATE vehiculos
     SET hora_salida=?, valor_pagado=?
     WHERE id=? AND hora_salida IS NULL");
mysqli_stmt_bind_param($up, "sdi", $horaSalida, $valor, $id);
mysqli_stmt_execute($up);

mensaje_flash('ok', "✅ Salida registrada. Total a cobrar: " . moneda($valor));

header("Location: ticket.php?id=" . $id);
exit;
