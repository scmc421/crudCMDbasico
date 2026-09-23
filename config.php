sudo tee /srv/http/tienda/config.php > /dev/null << 'EOF'
<?php
session_start();

function db() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . __DIR__ . '/tienda.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

function initDB() {
    $db = db();
    $db->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            rol TEXT NOT NULL DEFAULT 'cliente'
        );
        CREATE TABLE IF NOT EXISTS productos (
            idproducto INTEGER PRIMARY KEY AUTOINCREMENT,
            nombreproducto TEXT NOT NULL,
            valorunitario REAL NOT NULL
        );
        CREATE TABLE IF NOT EXISTS pedidos (
            idpedido INTEGER PRIMARY KEY AUTOINCREMENT,
            fecha TEXT NOT NULL,
            idproducto INTEGER NOT NULL,
            cantidad INTEGER NOT NULL,
            FOREIGN KEY(idproducto) REFERENCES productos(idproducto)
        );
        CREATE TABLE IF NOT EXISTS facturas (
            idfactura INTEGER PRIMARY KEY AUTOINCREMENT,
            fecha TEXT NOT NULL,
            idpedido INTEGER NOT NULL,
            total REAL NOT NULL,
            FOREIGN KEY(idpedido) REFERENCES pedidos(idpedido)
        );
    ");

    if (!$db->query("SELECT COUNT(*) FROM usuarios WHERE usuario='admin'")->fetchColumn()) {
        $db->prepare("INSERT INTO usuarios (usuario,password,rol) VALUES (?,?,?)")
           ->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'administrador']);
    }

    if (!$db->query("SELECT COUNT(*) FROM productos")->fetchColumn()) {
        $db->exec("INSERT INTO productos (nombreproducto, valorunitario) VALUES
            ('Laptop HP', 850.00),
            ('Mouse Logitech', 25.50),
            ('Teclado Mecánico', 75.00)");
    }
}

function requiereLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

function requiereAdmin() {
    requiereLogin();
    if ($_SESSION['rol'] !== 'administrador') {
        header('Location: index.php');
        exit;
    }
}

initDB();
EOF