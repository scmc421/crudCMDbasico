<?php
require 'config.php';
requiereLogin();
$msg = '';

// AGREGAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'agregar') {
    db()->prepare("INSERT INTO productos (nombreproducto, valorunitario) VALUES (?,?)")
        ->execute([trim($_POST['nombreproducto']), floatval($_POST['valorunitario'])]);
    $msg = "Producto agregado.";
}

// MODIFICAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'modificar') {
    db()->prepare("UPDATE productos SET nombreproducto=?, valorunitario=? WHERE idproducto=?")
        ->execute([trim($_POST['nombreproducto']), floatval($_POST['valorunitario']), intval($_POST['idproducto'])]);
    $msg = "Producto modificado.";
}

// ELIMINAR
if (isset($_GET['eliminar'])) {
    db()->prepare("DELETE FROM productos WHERE idproducto=?")->execute([intval($_GET['eliminar'])]);
    $msg = "Producto eliminado.";
}

// Cargar producto a modificar
$editar = null;
if (isset($_GET['editar'])) {
    $stmt = db()->prepare("SELECT * FROM productos WHERE idproducto=?");
    $stmt->execute([intval($_GET['editar'])]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

$productos = db()->query("SELECT * FROM productos ORDER BY idproducto")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Productos</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container">
    <header><h1>Productos</h1><a class="btn" href="index.php">Volver</a></header>
    <?php if ($msg): ?><p class="msg"><?= htmlspecialchars($msg) ?></p><?php endif; ?>

    <section class="card">
        <h2><?= $editar ? 'Modificar producto' : 'Agregar producto' ?></h2>
        <form method="post">
            <input type="hidden" name="accion" value="<?= $editar ? 'modificar' : 'agregar' ?>">
            <?php if ($editar): ?>
                <input type="hidden" name="idproducto" value="<?= $editar['idproducto'] ?>">
            <?php endif; ?>
            <label>Nombre producto:</label>
            <input type="text" name="nombreproducto"
                   value="<?= $editar ? htmlspecialchars($editar['nombreproducto']) : '' ?>" required>
            <label>Valor unitario:</label>
            <input type="number" step="0.01" name="valorunitario"
                   value="<?= $editar ? $editar['valorunitario'] : '' ?>" required>
            <button><?= $editar ? 'Guardar cambios' : 'Agregar' ?></button>
            <?php if ($editar): ?><a class="btn" href="productos.php">Cancelar</a><?php endif; ?>
        </form>
    </section>

    <section class="card">
        <h2>Lista de productos</h2>
        <table>
            <tr><th>ID</th><th>Nombre</th><th>Valor unitario</th><th>Acciones</th></tr>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td><?= $p['idproducto'] ?></td>
                <td><?= htmlspecialchars($p['nombreproducto']) ?></td>
                <td>$<?= number_format($p['valorunitario'], 2) ?></td>
                <td>
                    <a class="btn-mini" href="productos.php?editar=<?= $p['idproducto'] ?>">Modificar</a>
                    <a class="btn-mini rojo" href="productos.php?eliminar=<?= $p['idproducto'] ?>"
                       onclick="return confirm('¿Eliminar producto?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</div>
</body></html>