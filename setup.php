<?php
/**
 * FITXER DE CONFIGURACIÓ INICIAL
 * Executa'l una vegada per a crear la base de dades i les taules.
 * IMPORTANT: Elimina'l o protegeix-lo després de la configuració inicial!
 */

$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbName = 'jardins_llica';

echo '<!DOCTYPE html>
<html lang="ca">
<head><meta charset="UTF-8">
<title>Configuració inicial - Jardins de Lliçà</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
<div class="card shadow rounded-4 p-4" style="max-width:700px;margin:0 auto;">
<h2 class="fw-bold text-success mb-4">🌿 Configuració inicial - Jardins de Lliçà</h2>';

$steps   = [];
$success = true;

try {
    // Connectar sense BD específica
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $steps[] = ['ok', 'Connexió a MySQL establerta correctament'];

    // Crear BD
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $steps[] = ['ok', "Base de dades `$dbName` creada (o ja existia)"];
    $pdo->exec("USE `$dbName`");

    // Crear taules
    $tables = [
        "categories" => "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            description TEXT
        ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "products" => "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category_id INT,
            featured TINYINT(1) DEFAULT 0,
            image VARCHAR(255) DEFAULT 'no-image.png',
            active TINYINT(1) DEFAULT 1,
            stock INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "users" => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) DEFAULT NULL,
            dni VARCHAR(9) DEFAULT NULL,
            phone VARCHAR(9) DEFAULT NULL,
            address VARCHAR(255) DEFAULT NULL,
            city VARCHAR(100) DEFAULT NULL,
            postal_code VARCHAR(5) DEFAULT NULL,
            user_type ENUM('admin','company','individual') DEFAULT 'individual',
            company_cif VARCHAR(20) DEFAULT NULL,
            company_name VARCHAR(100) DEFAULT NULL,
            company_address VARCHAR(255) DEFAULT NULL,
            company_city VARCHAR(100) DEFAULT NULL,
            company_postal_code VARCHAR(5) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "offers" => "CREATE TABLE IF NOT EXISTS offers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            message TEXT,
            image VARCHAR(255) DEFAULT 'no-image.png',
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "orders" => "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            total DECIMAL(10,2) NOT NULL,
            shipping_name VARCHAR(100),
            shipping_dni VARCHAR(9),
            shipping_phone VARCHAR(9),
            shipping_address VARCHAR(255),
            shipping_city VARCHAR(100),
            shipping_postal_code VARCHAR(5),
            billing_cif VARCHAR(20) DEFAULT NULL,
            billing_company_name VARCHAR(100) DEFAULT NULL,
            billing_address VARCHAR(255) DEFAULT NULL,
            billing_city VARCHAR(100) DEFAULT NULL,
            billing_postal_code VARCHAR(5) DEFAULT NULL,
            status ENUM('pendent','confirmat','enviat','lliurat') DEFAULT 'pendent',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "order_items" => "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT DEFAULT NULL,
            product_name VARCHAR(200) NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "password_resets" => "CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    foreach ($tables as $tableName => $sql) {
        $pdo->exec($sql);
        $steps[] = ['ok', "Taula `$tableName` creada"];
    }

    // Inserir dades inicials
    $countCat = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($countCat == 0) {
        $cats = [
            ['Plantes d\'Interior', 'plantes-interior', 'Plantes perfectes per a decorar la llar'],
            ['Plantes d\'Exterior', 'plantes-exterior', 'Plantes robustes per a jardins i terrasses'],
            ['Llavors',             'llavors',          'Llavors de qualitat per al vostre hort'],
            ['Eines de Jardí',      'eines-jardi',      'Tot el que necessiteu per a treballar el jardí'],
            ['Testos i Jardineres', 'testos-jardineres','Testos i jardineres de tots els mides i materials'],
            ['Fertilitzants',       'fertilitzants',    'Adobs i fertilitzants per a les vostres plantes'],
        ];
        $stmtCat = $pdo->prepare("INSERT INTO categories (name,slug,description) VALUES (?,?,?)");
        foreach ($cats as $c) $stmtCat->execute($c);
        $steps[] = ['ok', count($cats) . ' categories afegides'];
    } else {
        $steps[] = ['info', 'Categories ja existien, no s\'han afegit de noves'];
    }

    // Productes de mostra
    $countProd = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($countProd == 0) {
        $products = [
            ['Monstera Deliciosa','La planta tropical per excel·lència. Ideal per a salons amb molta llum indirecta.',24.99,1,1,15],
            ['Ficus Lyrata (Figuera de Fulla Violí)','Planta d\'interior de gran port. Necessita llum abundant i reg moderat.',39.99,1,1,8],
            ['Pothos Daurat','Planta enfiladissa perfecta per a principiants. Tolera poca llum.',9.99,1,0,30],
            ['Calathea Orbifolia','Planta ornamental amb fulles ratllades. Necessita humitat i llum indirecta.',18.50,1,0,12],
            ['Lavanda','La planta aromàtica mediterrània per excel·lència. Perfecta per a jardins assolellats.',7.99,2,1,50],
            ['Romaní','Herba aromàtica i culinària molt resistent. Ideal per a jardins secs.',5.99,2,0,45],
            ['Rosella','Flor silvestre de color vermell intens. Molt fàcil de cultivar.',6.50,2,0,35],
            ['Olivera Nana','Varietat compacta de l\'olivera mediterrània. Perfecta per a terrasses.',29.99,2,1,10],
            ['Llavors de Tomàquet Cherry','Varietat molt productiva. 50 llavors per sobre.',3.99,3,0,100],
            ['Llavors d\'Enciam Batavia','Enciam cruixent i saborós. Ideal per a horts urbans. 200 llavors.',2.50,3,0,80],
            ['Llavors de Gira-sol','Gira-sol clàssic de tija alta. Perfecte per a jardins. 30 llavors.',2.99,3,0,60],
            ['Rastel de Jardí Professional','Rastel amb mànec de fusta de 130cm i 14 pues d\'acer inoxidable.',22.95,4,0,20],
            ['Tisores de Poda INOX','Tisores d\'acer inoxidable amb empunyadura ergonòmica.',16.99,4,0,25],
            ['Pala de Mà Set 3 peces','Set de 3 peces: pala de mà, rastell i trasplantador.',14.50,4,0,30],
            ['Test Ceràmica Blanc 20cm','Test de ceràmica blanc mat amb forat de drenatge. Diàmetre 20cm.',11.99,5,0,40],
            ['Jardinera de Terracota 40cm','Jardinera de terracota natural, acabat rústic. 40x20x20cm.',18.95,5,0,25],
            ['Test Penjant Macramé','Test 15cm amb penjador de macramé fet a mà.',15.99,5,1,15],
            ['Adob Universal Líquid 1L','Adob líquid per a tota mena de plantes. Per a 100 regs.',8.99,6,0,50],
            ['Substrat Universal 20L','Substrat enriquit amb turba, perlita i compost. Sac 20L.',12.99,6,0,35],
            ['Fertilitzant Orgànic Roses 1kg','Granulat orgànic especialment formulat per a roses.',10.50,6,0,30],
        ];
        $stmtProd = $pdo->prepare("INSERT INTO products (name,description,price,category_id,featured,stock) VALUES (?,?,?,?,?,?)");
        foreach ($products as $pr) $stmtProd->execute($pr);
        $steps[] = ['ok', count($products) . ' productes de mostra afegits'];
    } else {
        $steps[] = ['info', 'Productes ja existien'];
    }

    // Usuari admin
    $adminExists = $pdo->query("SELECT id FROM users WHERE username='admin'")->fetch();
    if (!$adminExists) {
        $adminPass = password_hash('Admin1234!', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username,email,password,full_name,user_type) VALUES (?,?,?,?,?)")
            ->execute(['admin','admin@jardinsllica.cat',$adminPass,'Administrador Web','admin']);
        $steps[] = ['ok', 'Usuari admin creat (usuari: admin, clau: Admin1234!)'];
    } else {
        $steps[] = ['info', 'Usuari admin ja existia'];
    }

    // Ofertes de mostra
    $countOffers = $pdo->query("SELECT COUNT(*) FROM offers")->fetchColumn();
    if ($countOffers == 0) {
        $pdo->exec("INSERT INTO offers (name,message) VALUES
            ('Primavera a Lliçà', '20% de descompte en totes les plantes d\\'exterior! Aprofita l\\'inici de la primavera.'),
            ('Set de Jardineria', 'Compra el rastel + les tisores i emporta\\'t la pala de mà GRATIS!')");
        $steps[] = ['ok', '2 ofertes de mostra afegides'];
    }

} catch (PDOException $e) {
    $steps[] = ['error', 'Error: ' . $e->getMessage()];
    $success = false;
}

foreach ($steps as $step) {
    $icon  = $step[0] === 'ok' ? '✅' : ($step[0] === 'info' ? 'ℹ️' : '❌');
    $class = $step[0] === 'ok' ? 'success' : ($step[0] === 'info' ? 'info' : 'danger');
    echo "<div class=\"alert alert-$class py-2 mb-2\">$icon {$step[1]}</div>";
}

if ($success) {
    echo '
    <div class="alert alert-success mt-4">
        <h5 class="fw-bold">✅ Configuració completada!</h5>
        <p class="mb-2">La base de dades s\'ha creat correctament.</p>
        <p class="mb-2"><strong>Credencials de l\'administrador:</strong><br>
        Usuari: <code>admin</code> · Clau: <code>Admin1234!</code></p>
        <div class="d-flex gap-2 mt-3">
            <a href="/index.php" class="btn btn-success">🌿 Anar a la botiga</a>
            <a href="/login.php" class="btn btn-outline-success">Iniciar sessió</a>
        </div>
    </div>
    <div class="alert alert-warning mt-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Seguretat:</strong> Elimina o reanomena aquest fitxer (setup.php) per seguretat!
    </div>';
} else {
    echo '<div class="alert alert-danger mt-4"><strong>❌ Hi ha hagut errors.</strong> Revisa la configuració de la base de dades a <code>config/database.php</code>.</div>';
}

echo '</div></div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body></html>';
