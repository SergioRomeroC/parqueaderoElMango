<?php

/* =========================================================
   CONFIGURACIÓN Y FUNCIONES COMPARTIDAS
   ========================================================= */

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

date_default_timezone_set('America/Bogota');

// ---- Configuración general (edite aquí) ----

define('NOMBRE_NEGOCIO', 'Parqueadero El Mango');
define('CAPACIDAD', 200);          // cupos totales
define('AVISO_CUPOS', 20);         // avisa cuando queden estos cupos o menos

define('ADMIN_USUARIO', 'admin');
// Hash de la clave actual (040506). Para cambiarla: php -r "echo password_hash('NUEVA', PASSWORD_DEFAULT);"
define('ADMIN_HASH', '$2y$10$3u22GZc0vNeSggtABWDRaedS92yfgtnbinEAoKzp.TtJRV7q50vV.');

// Tarifas sugeridas al registrar (solo son un punto de partida, se pueden editar al guardar).
$TARIFAS_SUGERIDAS = [
    'Carro'     => ['minuto' => 100, 'plena' => 8000],
    'Moto'      => ['minuto' => 50,  'plena' => 4000],
    'Bicicleta' => ['minuto' => 20,  'plena' => 1000],
];

// ---- Utilidades ----

function e($texto){
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function moneda($valor){
    return '$ ' . number_format((float)$valor, 0, ',', '.');
}

function requiere_login(){
    if(!isset($_SESSION['admin'])){
        header("Location: login.php");
        exit;
    }
}

function solo_post(){
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        header("Location: listar.php");
        exit;
    }
}

function minutos_cobrables($entradaTs, $salidaTs){
    $m = (int)ceil(($salidaTs - $entradaTs) / 60);
    return max(1, $m);
}

function calcular_valor($tarifaTipo, $tarifaValor, $entradaTs, $salidaTs){
    if($tarifaTipo === 'minuto'){
        return minutos_cobrables($entradaTs, $salidaTs) * (float)$tarifaValor;
    }
    return (float)$tarifaValor; // tarifa plena
}

function formato_duracion($minutos){
    $minutos = max(0, (int)$minutos);
    $h = intdiv($minutos, 60);
    $m = $minutos % 60;
    if($h > 0){
        return $h . ' h ' . $m . ' min';
    }
    return $m . ' min';
}

function etiqueta_tarifa($tipo){
    return $tipo === 'plena' ? 'Plena' : 'Por minuto';
}

function icono_tipo($tipo){
    switch($tipo){
        case 'Carro':     return '🚙';
        case 'Moto':      return '🏍';
        case 'Bicicleta': return '🚲';
    }
    return '🚗';
}

function escalar($conn, $sql, $tipos = '', $params = []){
    $st = mysqli_prepare($conn, $sql);
    if($tipos !== ''){
        mysqli_stmt_bind_param($st, $tipos, ...$params);
    }
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    $fila = mysqli_fetch_row($res);
    mysqli_stmt_close($st);
    return $fila ? $fila[0] : 0;
}

function token_csrf(){
    if(empty($_SESSION['csrf'])){
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function campo_csrf(){
    return '<input type="hidden" name="csrf" value="' . e(token_csrf()) . '">';
}

function verificar_csrf(){
    $ok = isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
    if(!$ok){
        http_response_code(400);
        die("Solicitud no válida. Vuelva atrás y recargue la página.");
    }
}

function mensaje_flash($tipo = null, $texto = null){
    if($tipo !== null){
        $_SESSION['flash'] = [$tipo, $texto];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
