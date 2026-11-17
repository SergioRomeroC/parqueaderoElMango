<?php

include 'db/conexion.php';
requiere_login();

$estado = $_GET['estado'] ?? 'todos';
if(!in_array($estado, ['todos', 'activos', 'finalizados'], true)){
    $estado = 'todos';
}

$where = "";
if($estado === 'activos'){
    $where = "WHERE hora_salida IS NULL";
}elseif($estado === 'finalizados'){
    $where = "WHERE hora_salida IS NOT NULL";
}

$resultado = mysqli_query($conn,
    "SELECT * FROM vehiculos $where ORDER BY id DESC LIMIT 500");

$nTodos = (int)escalar($conn, "SELECT COUNT(*) FROM vehiculos");
$nActivos = (int)escalar($conn, "SELECT COUNT(*) FROM vehiculos WHERE hora_salida IS NULL");

$titulo = "Vehículos";
$subtitulo = "Listado general de vehículos";
$activo = "listar";

include 'inc/header.php';
?>

<div class="herramientas">

    <div class="filtros">
        <a href="listar.php" class="<?php echo $estado === 'todos' ? 'activo' : ''; ?>">Todos (<?php echo $nTodos; ?>)</a>
        <a href="listar.php?estado=activos" class="<?php echo $estado === 'activos' ? 'activo' : ''; ?>">🅿 Dentro (<?php echo $nActivos; ?>)</a>
        <a href="listar.php?estado=finalizados" class="<?php echo $estado === 'finalizados' ? 'activo' : ''; ?>">✔ Finalizados (<?php echo $nTodos - $nActivos; ?>)</a>
    </div>

    <input type="text" id="buscar" placeholder="🔍 Buscar por placa o propietario..."
           onkeyup="buscarVehiculo()">

    <a class="btn-volver" href="registrar.php" style="margin-left:auto">➕ Registrar vehículo</a>

</div>

<div class="tabla-wrap">

<table id="tablaVehiculos">

<thead>
<tr>
    <th>ID</th>
    <th>Placa</th>
    <th>Tipo</th>
    <th>Propietario</th>
    <th>Entrada</th>
    <th>Salida</th>
    <th>Tiempo</th>
    <th>Tarifa</th>
    <th>Valor</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>
<?php

$ahora = time();

while($fila = mysqli_fetch_assoc($resultado)){

    $entradaTs = strtotime($fila['hora_entrada']);
    $dentro = empty($fila['hora_salida']);
    $finTs = $dentro ? $ahora : strtotime($fila['hora_salida']);

    $minutos = minutos_cobrables($entradaTs, $finTs);

    if($dentro){
        $valor = calcular_valor($fila['tarifa_tipo'], $fila['tarifa_valor'], $entradaTs, $ahora);
    }else{
        $valor = $fila['valor_pagado'];
    }

    $buscar = $fila['placa'] . ' ' . $fila['propietario'];
?>
<tr data-buscar="<?php echo e($buscar); ?>">

    <td><?php echo (int)$fila['id']; ?></td>
    <td><span class="placa"><?php echo e($fila['placa']); ?></span></td>
    <td><?php echo icono_tipo($fila['tipo']) . ' ' . e($fila['tipo']); ?></td>
    <td><?php echo e($fila['propietario']); ?></td>
    <td><?php echo e(date('d/m/Y H:i', $entradaTs)); ?></td>
    <td><?php echo $dentro ? '—' : e(date('d/m/Y H:i', $finTs)); ?></td>

    <td>
        <?php if($dentro){ ?>
            <span data-entrada="<?php echo $entradaTs; ?>" data-id="<?php echo (int)$fila['id']; ?>"><?php echo e(formato_duracion($minutos)); ?></span>
        <?php }else{ echo e(formato_duracion($minutos)); } ?>
    </td>

    <td>
        <?php echo etiqueta_tarifa($fila['tarifa_tipo']); ?>
        <div class="suave" style="font-size:12px"><?php echo moneda($fila['tarifa_valor']); ?></div>
    </td>

    <td>
        <?php if($dentro){ ?>
            <span class="suave" style="font-size:12px">Estimado</span><br>
            <b data-estimado="<?php echo (int)$fila['id']; ?>"
               data-modo="<?php echo e($fila['tarifa_tipo']); ?>"
               data-valor="<?php echo e($fila['tarifa_valor']); ?>"><?php echo moneda($valor); ?></b>
        <?php }else{ ?>
            <b><?php echo moneda($valor); ?></b>
        <?php } ?>
    </td>

    <td>
        <?php if($dentro){ ?>
            <span class="badge dentro">● Dentro</span>
        <?php }else{ ?>
            <span class="badge salio">Salió</span>
        <?php } ?>
    </td>

    <td style="white-space:nowrap">

        <?php if($dentro){ ?>

            <form class="en-linea" method="POST" action="salida.php" onsubmit="return confirmarSalida()">
                <?php echo campo_csrf(); ?>
                <input type="hidden" name="id" value="<?php echo (int)$fila['id']; ?>">
                <button type="submit" class="btn-salida">🚗 Salida</button>
            </form>

        <?php }else{ ?>

            <a class="btn-ticket" href="ticket.php?id=<?php echo (int)$fila['id']; ?>">🖨 Ticket</a>

        <?php } ?>

        <form class="en-linea" method="POST" action="eliminar.php" onsubmit="return confirmarEliminar()">
            <?php echo campo_csrf(); ?>
            <input type="hidden" name="id" value="<?php echo (int)$fila['id']; ?>">
            <button type="submit" class="btn-eliminar">🗑</button>
        </form>

    </td>

</tr>
<?php } ?>
</tbody>

</table>

<p id="sinResultados" class="suave" style="display:none;text-align:center;padding:20px">
    No se encontraron vehículos con esa búsqueda.
</p>

</div>

<?php include 'inc/footer.php'; ?>
