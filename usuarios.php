<?php
require 'config.php';
requiereAdmin(); // solo admin
$msg = '';

// AGREGAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'agregar') {
    try {
        db()->prepare("INSERT INTO usuarios (usuario,password,rol) VALUES (?,?,?)")
            ->execute([
                trim($_POST['usuario']),
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['rol']
            ]);
        $msg = "Usuario agregado.";
    } catch (PDOException $e) {
        $msg = "Ese usuario ya existe.";
    }
}

// MODIFICAR (solo rol y contraseña opcional)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'modificar') {
    $id = intval($_POST['id']);
    if (!empty($_POST['password'])) {
        db()->prepare("UPDATE usuarios SET usuario=?, password=?, rol=? WHERE id=?")
            ->execute([trim($_POST['usuario']), password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['rol'], $id]);
    } else {
        db()->prepare("UPDATE usuarios SET usuario=?, rol=? WHERE id=?")
            ->execute([trim($_POST['usuario']), $_POST['rol'], $id]);
    }
    $msg = "Usuario modificado.";
}

// ELIMINAR
if (isset($_GET['eliminar'])) {
    if (intval($_GET['eliminar']) == $_SESSION['usuario_id']) {
        $msg = "No puedes eliminar tu propio usuario.";
    } else {
        db()->prepare("DELETE FROM usuarios WHERE id=?")->execute([intval($_GET['eliminar'])]);
        $msg = "Usuario eliminado.";
    }
}

// Cargar para editar
$editar = null;
if (isset($_GET['editar'])) {
    $stmt = db()->prepare("SELECT * FROM usuarios WHERE id=?");
    $stmt->execute([intval($_GET['editar'])]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

$usuarios = db()->query("SELECT * FROM usuarios ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Usuarios</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container">
    <header><h1>Usuarios</h1><a class="btn" href="index.php">Volver</a></header>
    <?php if ($msg): ?><p class="msg"><?= htmlspecialchars($msg) ?></p><?php endif; ?>

    <section class="card">
        <h2><?= $editar ? 'Modificar usuario' : 'Agregar usuario' ?></h2>
        <form method="post">
            <input type="hidden" name="accion" value="<?= $editar ? 'modificar' : 'agregar' ?>">
            <?php if ($editar): ?><input type="hidden" name="id" value="<?= $editar['id'] ?>"><?php endif; ?>

            <label>Usuario:</label>
            <input type="text" name="usuario" value="<?= $editar ? htmlspecialchars($editar['usuario']) : '' ?>" required>

            <label>Contraseña: <?= $editar ? '(dejar vacío para no cambiar)' : '' ?></label>
            <input type="password" name="password" <?= $editar ? '' : 'required' ?>>

            <label>Rol:</label>
            <select name="rol">
                <option value="cliente" <?= ($editar && $editar['rol']=='cliente')?'selected':'' ?>>Cliente</option>
                <option value="administrador" <?= ($editar && $editar['rol']=='administrador')?'selected':'' ?>>Administrador</option>
            </select>

            <button><?= $editar ? 'Guardar cambios' : 'Agregar' ?></button>
            <?php if ($editar): ?><a class="btn" href="usuarios.php">Cancelar</a><?php endif; ?>
        </form>
    </section>

    <section class="card">
        <h2>Lista de usuarios</h2>
        <table>
            <tr><th>ID</th><th>Usuario</th><th>Rol</th><th>Acciones</th></tr>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['usuario']) ?></td>
                <td><?= htmlspecialchars($u['rol']) ?></td>
                <td>
                    <a class="btn-mini" href="usuarios.php?editar=<?= $u['id'] ?>">Modificar</a>
                    <a class="btn-mini rojo" href="usuarios.php?eliminar=<?= $u['id'] ?>"
                       onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</div>
</body></html>