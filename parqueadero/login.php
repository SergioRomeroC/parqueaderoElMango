<?php

require_once 'db/funciones.php';

if(isset($_SESSION['admin'])){
    header("Location: index.php");
    exit;
}

$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = $_POST['clave'] ?? '';

    // Freno simple contra fuerza bruta: espera tras 5 intentos fallidos
    $intentos = $_SESSION['intentos'] ?? 0;
    $bloqueoHasta = $_SESSION['bloqueo'] ?? 0;

    if(time() < $bloqueoHasta){

        $error = "Demasiados intentos. Espere " . ($bloqueoHasta - time()) . " segundos.";

    }elseif($usuario === ADMIN_USUARIO && password_verify($clave, ADMIN_HASH)){

        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        unset($_SESSION['intentos'], $_SESSION['bloqueo']);

        header("Location: index.php");
        exit;

    }else{

        $intentos++;
        $_SESSION['intentos'] = $intentos;

        if($intentos >= 5){
            $_SESSION['bloqueo'] = time() + 30;
            $_SESSION['intentos'] = 0;
        }

        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingresar · <?php echo e(NOMBRE_NEGOCIO); ?></title>
<link rel="icon" href="img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Russo+One&display=swap" rel="stylesheet">
<script>
try{
    document.documentElement.setAttribute('data-theme',
        localStorage.getItem('tema_mango') === 'light' ? 'light' : 'dark');
}catch(e){}
</script>
<link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<div class="login-wrap">

    <div class="login-card">

        <img src="img/logo.jpg" alt="Festival del Mango · El Triunfo · Parqueadero de motos">

        <h2 style="margin-top:14px">Panel del Parqueadero</h2>
        <p class="suave" style="margin-top:0">Ingrese para administrar entradas y salidas</p>

        <?php if($error !== ""){ ?>
            <div class="mensaje error"><?php echo e($error); ?></div>
        <?php } ?>

        <form method="POST" autocomplete="off">

            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" placeholder="Usuario" required autofocus>

            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="clave" placeholder="••••••" required>

            <button type="submit" class="principal">🔑 Ingresar</button>

        </form>

    </div>

</div>

</body>
</html>
