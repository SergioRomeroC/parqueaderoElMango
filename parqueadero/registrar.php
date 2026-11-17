<?php

include 'db/conexion.php';
requiere_login();

$errores = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    verificar_csrf();

    $placa = strtoupper(trim($_POST['placa'] ?? ''));
    $placa = preg_replace('/[^A-Z0-9-]/', '', $placa);
    $tipo = $_POST['tipo'] ?? '';
    $propietario = trim($_POST['propietario'] ?? '');
    $tarifa_tipo = $_POST['tarifa_tipo'] ?? '';
    $tarifa_valor = (float)($_POST['tarifa_valor'] ?? 0);

    if(strlen($placa) < 2 || strlen($placa) > 10){
        $errores[] = "La placa debe tener entre 2 y 10 caracteres (letras y números).";
    }

    if(!isset($TARIFAS_SUGERIDAS[$tipo])){
        $errores[] = "Tipo de vehículo no válido.";
    }

    if($propietario === ''){
        $errores[] = "Escriba el nombre del propietario.";
    }

    if(!in_array($tarifa_tipo, ['minuto', 'plena'], true)){
        $errores[] = "Tipo de tarifa no válido.";
    }

    if($tarifa_valor <= 0){
        $errores[] = "El valor de la tarifa debe ser mayor que 0.";
    }

    if(!$errores){

        // ¿Ya está dentro?
        $yaDentro = (int)escalar($conn,
            "SELECT COUNT(*) FROM vehiculos WHERE placa=? AND hora_salida IS NULL",
            "s", [$placa]);

        if($yaDentro > 0){
            $errores[] = "La placa $placa ya se encuentra dentro del parqueadero.";
        }

        // ¿Hay cupo?
        $activos = (int)escalar($conn, "SELECT COUNT(*) FROM vehiculos WHERE hora_salida IS NULL");
        if($activos >= CAPACIDAD){
            $errores[] = "El parqueadero está lleno (" . CAPACIDAD . " cupos).";
        }
    }

    if(!$errores){

        $ahora = date('Y-m-d H:i:s');

        $st = mysqli_prepare($conn,
            "INSERT INTO vehiculos (placa, tipo, propietario, hora_entrada, tarifa_tipo, tarifa_valor)
             VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, "sssssd",
            $placa, $tipo, $propietario, $ahora, $tarifa_tipo, $tarifa_valor);

        if(mysqli_stmt_execute($st)){
            $nuevoId = mysqli_insert_id($conn);
            mensaje_flash('ok', "✅ Vehículo $placa registrado correctamente");
            header("Location: ticket.php?id=" . $nuevoId);
            exit;
        }

        $errores[] = "No se pudo guardar el vehículo. Intente de nuevo.";
    }
}

$titulo = "Registrar entrada";
$subtitulo = "Ingreso de vehículos";
$activo = "registrar";
$estrecho = true;
$extraJs = true;

include 'inc/header.php';
?>

<script>
const TARIFAS_SUGERIDAS = <?php echo json_encode($TARIFAS_SUGERIDAS); ?>;
</script>

<div class="card acento">

    <h2>🏍 Nuevo vehículo</h2>

    <?php foreach($errores as $err){ ?>
        <div class="mensaje error"><?php echo e($err); ?></div>
    <?php } ?>

    <form method="POST" autocomplete="off">

        <?php echo campo_csrf(); ?>

        <label for="placa">Placa</label>
        <input type="text" id="placa" name="placa" placeholder="Ej: ABC123" maxlength="10"
               value="<?php echo e($_POST['placa'] ?? ''); ?>" required autofocus
               style="text-transform:uppercase">

        <label for="tipo">Tipo de vehículo</label>
        <select id="tipo" name="tipo">
            <?php foreach(array_keys($TARIFAS_SUGERIDAS) as $t){ ?>
                <option value="<?php echo e($t); ?>" <?php echo (($_POST['tipo'] ?? 'Moto') === $t) ? 'selected' : ''; ?>>
                    <?php echo icono_tipo($t) . ' ' . e($t); ?>
                </option>
            <?php } ?>
        </select>

        <label for="propietario">Propietario</label>
        <input type="text" id="propietario" name="propietario" placeholder="Nombre del propietario"
               value="<?php echo e($_POST['propietario'] ?? ''); ?>" required>

        <div class="fila-2">
            <div>
                <label for="tarifa_tipo">Tipo de tarifa</label>
                <select id="tarifa_tipo" name="tarifa_tipo">
                    <option value="minuto" <?php echo (($_POST['tarifa_tipo'] ?? '') === 'minuto') ? 'selected' : ''; ?>>⏱ Por minuto</option>
                    <option value="plena" <?php echo (($_POST['tarifa_tipo'] ?? '') === 'plena') ? 'selected' : ''; ?>>💰 Tarifa plena</option>
                </select>
            </div>
            <div>
                <label for="tarifa_valor">Valor tarifa ($)</label>
                <input type="number" id="tarifa_valor" name="tarifa_valor" min="1" step="1"
                       placeholder="Ej: 100" value="<?php echo e($_POST['tarifa_valor'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="ayuda" id="notaTarifa"></div>

        <button type="submit" class="principal">💾 Guardar y generar ticket</button>

    </form>

</div>

<?php include 'inc/footer.php'; ?>
