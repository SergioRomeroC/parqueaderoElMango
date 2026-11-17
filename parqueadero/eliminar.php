<?php

include 'db/conexion.php';
requiere_login();
solo_post();
verificar_csrf();

$id = (int)($_POST['id'] ?? 0);

$st = mysqli_prepare($conn, "DELETE FROM vehiculos WHERE id=?");
mysqli_stmt_bind_param($st, "i", $id);
mysqli_stmt_execute($st);

mensaje_flash('ok', "🗑 Registro eliminado");

header("Location: listar.php");
exit;
