<?php
require 'config.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario']);
    $password = $_POST['password'];

    $stmt = db()->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($u) {
        if (password_verify($password, $u['password'])) {
            $_SESSION['usuario_id'] = $u['id'];
            $_SESSION['usuario']    = $u['usuario'];
            $_SESSION['rol']        = $u['rol'];
            header('Location: index.php');
            exit;
        } else {
            $msg = "Contraseña incorrecta.";
        }
    } else {
        db()->prepare("INSERT INTO usuarios (usuario,password,rol) VALUES (?,?,'cliente')")
            ->execute([$usuario, password_hash($password, PASSWORD_DEFAULT)]);
        $msg = "Usuario creado. Ya puedes iniciar sesión.";
    }
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Login</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container">
    <h1>Iniciar sesión</h1>
    <?php if ($msg): ?><p class="msg"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
    <form method="post">
        <label>Usuario:</label><input type="text" name="usuario" required>
        <label>Contraseña:</label><input type="password" name="password" required>
        <button>Entrar / Registrarse</button>
    </form>
    <p class="hint">Si el usuario no existe se crea como cliente.<br>
    Admin: <b>admin</b> / <b>admin123</b></p>
</div>
</body></html>