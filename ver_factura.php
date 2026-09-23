<?php
require 'config.php';
requiereLogin();

$idfactura = intval($_GET['id'] ?? 0);

// Datos de la factura + pedido + producto
$stmt = db()->prepare("
    SELECT f.idfactura, f.fecha, f.idpedido, f.total,
           p.cantidad,
           pr.idproducto, pr.nombreproducto, pr.valorunitario
    FROM facturas f
    JOIN pedidos p   ON p.idpedido   = f.idpedido
    JOIN productos pr ON pr.idproducto = p.idproducto
    WHERE f.idfactura = ?
");
$stmt->execute([$idfactura]);
$f = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$f) {
    die("Factura no encontrada.");
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Factura #<?= $f['idfactura'] ?></title>
<link rel="stylesheet" href="style.css">
<style>
    @media print {
        .no-print { display: none; }
        body { background: #fff; }
        .container { box-shadow: none; }
    }
</style>
</head><body>
<div class="container">
    <div class="no-print" style="text-align:right;">
        <button onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
        <a class="btn" href="facturas.php">Volver</a>
    </div>

    <h1 style="text-align:center;">FACTURA DE VENTA</h1>
    <hr>

    <table class="factura-cabecera">
        <tr><th>ID Factura:</th><td><?= $f['idfactura'] ?></td></tr>
        <tr><th>Fecha:</th><td><?= $f['fecha'] ?></td></tr>
        <tr><th>ID Pedido:</th><td><?= $f['idpedido'] ?></td></tr>
    </table>

    <h2>Detalle</h2>
    <table>
        <tr>
            <th>ID Producto</th>
            <th>Producto</th>
            <th>Valor unitario</th>
            <th>Cantidad</th>
            <th>Valor total</th>
        </tr>
        <tr>
            <td><?= $f['idproducto'] ?></td>
            <td><?= htmlspecialchars($f['nombreproducto']) ?></td>
            <td>$<?= number_format($f['valorunitario'],2) ?></td>
            <td><?= $f['cantidad'] ?></td>
            <td>$<?= number_format($f['valorunitario'] * $f['cantidad'],2) ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align:right;">TOTAL A PAGAR</th>
            <th>$<?= number_format($f['total'],2) ?></th>
        </tr>
    </table>

    <p class="hint" style="text-align:center; margin-top:20px;">
        Gracias por su compra — Documento generado automáticamente.
    </p>
</div>
</body></html>