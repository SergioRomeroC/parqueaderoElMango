<?php

include 'db/conexion.php';
requiere_login();
solo_post();
verificar_csrf();

/*
 * CIERRE DE CAJA
 * Antes esto ponía valor_pagado = 0 en TODOS los registros, y se perdía el
 * historial y los reportes. Ahora solo marca como "cerrado" lo ya cobrado:
 * el recaudo pendiente vuelve a $ 0 pero el historial queda intacto.
 */

$pendiente = (float)escalar($conn,
    "SELECT COALESCE(SUM(valor_pagado),0) FROM vehiculos WHERE cerrado=0 AND hora_salida IS NOT NULL");

mysqli_query($conn,
    "UPDATE vehiculos SET cerrado=1 WHERE cerrado=0 AND hora_salida IS NOT NULL");

mensaje_flash('ok', "💰 Caja cerrada. Se cerraron " . moneda($pendiente) . ".");

header("Location: dashboard.php");
exit;
