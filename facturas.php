<?php
require 'config.php';
requiereLogin();
$msg = '';

if (isset($_GET['eliminar'])) {
    db()->prepare("DELETE FROM facturas WHERE idfactura=?")->execute([intval($_GET['eliminar'])]);
    $msg = "Factura eliminada.";
}

$facturas = db()->query("SELECT * FROM facturas ORDER BY idfactura DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Facturas</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container">
    <header><h1>Facturas</h1><a class="btn" href="index.php">Volver</a></header>
    <?php if ($msg): ?><p class="msg"><?= htmlspecialchars($msg) ?></p><?php endif; ?>

    <section class="card">
        <h2>Lista de facturas</h2>
        <table>
            <tr><th>ID Factura</th><th>Fecha</th><th>ID Pedido</th><th>Total</th><th>Acciones</th></tr>
            <?php foreach ($facturas as $f): ?>
            <tr>
                <td><?= $f['idfactura'] ?></td>
                <td><?= $f['fecha'] ?></td>
                <td><?= $f['idpedido'] ?></td>
                <td>$<?= number_format($f['total'],2) ?></td>
                <td>
                    <a class="btn-mini" href="ver_factura.php?id=<?= $f['idfactura'] ?>">Ver factura</a>
                    <a class="btn-mini rojo" href="facturas.php?eliminar=<?= $f['idfactura'] ?>"
                       onclick="return confirm('¿Eliminar factura?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</div>
</body></html>