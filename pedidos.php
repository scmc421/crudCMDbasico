<?php
require 'config.php';
requiereLogin();
$msg = '';

// AGREGAR PEDIDO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'agregar') {
    db()->prepare("INSERT INTO pedidos (fecha, idproducto, cantidad) VALUES (?,?,?)")
        ->execute([date('Y-m-d H:i:s'), intval($_POST['idproducto']), intval($_POST['cantidad'])]);
    $msg = "Pedido registrado.";
}

// ELIMINAR
if (isset($_GET['eliminar'])) {
    db()->prepare("DELETE FROM pedidos WHERE idpedido=?")->execute([intval($_GET['eliminar'])]);
    $msg = "Pedido eliminado.";
}

// GENERAR FACTURA desde un pedido
if (isset($_GET['facturar'])) {
    $idpedido = intval($_GET['facturar']);
    $stmt = db()->prepare("
        SELECT p.idpedido, p.cantidad, pr.valorunitario
        FROM pedidos p
        JOIN productos pr ON pr.idproducto = p.idproducto
        WHERE p.idpedido = ?
    ");
    $stmt->execute([$idpedido]);
    $ped = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ped) {
        $total = $ped['valorunitario'] * $ped['cantidad'];
        db()->prepare("INSERT INTO facturas (fecha, idpedido, total) VALUES (?,?,?)")
            ->execute([date('Y-m-d H:i:s'), $idpedido, $total]);
        $msg = "Factura generada correctamente.";
    } else {
        $msg = "Pedido no encontrado.";
    }
}

// Lista de pedidos con datos del producto
$pedidos = db()->query("
    SELECT p.idpedido, p.fecha, p.cantidad,
           pr.idproducto, pr.nombreproducto, pr.valorunitario
    FROM pedidos p
    JOIN productos pr ON pr.idproducto = p.idproducto
    ORDER BY p.idpedido DESC
")->fetchAll(PDO::FETCH_ASSOC);

$productos = db()->query("SELECT * FROM productos ORDER BY nombreproducto")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Pedidos</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container">
    <header><h1>Pedidos</h1><a class="btn" href="index.php">Volver</a></header>
    <?php if ($msg): ?><p class="msg"><?= htmlspecialchars($msg) ?></p><?php endif; ?>

    <section class="card">
        <h2>Nuevo pedido</h2>
        <form method="post">
            <input type="hidden" name="accion" value="agregar">
            <label>Producto:</label>
            <select name="idproducto" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($productos as $p): ?>
                    <option value="<?= $p['idproducto'] ?>">
                        <?= htmlspecialchars($p['nombreproducto']) ?> — $<?= number_format($p['valorunitario'],2) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Cantidad:</label>
            <input type="number" name="cantidad" value="1" min="1" required>
            <button>Guardar pedido</button>
        </form>
    </section>

    <section class="card">
        <h2>Lista de pedidos</h2>
        <table>
            <tr>
                <th>ID Pedido</th><th>Fecha</th><th>ID Producto</th>
                <th>Producto</th><th>Valor unitario</th><th>Cantidad</th>
                <th>Valor total</th><th>Acciones</th>
            </tr>
            <?php foreach ($pedidos as $p): ?>
            <tr>
                <td><?= $p['idpedido'] ?></td>
                <td><?= $p['fecha'] ?></td>
                <td><?= $p['idproducto'] ?></td>
                <td><?= htmlspecialchars($p['nombreproducto']) ?></td>
                <td>$<?= number_format($p['valorunitario'],2) ?></td>
                <td><?= $p['cantidad'] ?></td>
                <td>$<?= number_format($p['valorunitario'] * $p['cantidad'],2) ?></td>
                <td>
                    <a class="btn-mini" href="pedidos.php?facturar=<?= $p['idpedido'] ?>">Generar factura</a>
                    <a class="btn-mini rojo" href="pedidos.php?eliminar=<?= $p['idpedido'] ?>"
                       onclick="return confirm('¿Eliminar pedido?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</div>
</body></html>