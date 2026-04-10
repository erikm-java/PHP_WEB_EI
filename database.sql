-- =============================================
-- Jardins de Lliçà - Base de Dades
-- =============================================

CREATE DATABASE IF NOT EXISTS jardins_llica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jardins_llica;

-- Taula de categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT
);

-- Taula de productes
CREATE TABLE IF NOT EXISTS products (
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
);

-- Taula d'usuaris
CREATE TABLE IF NOT EXISTS users (
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
    user_type ENUM('admin', 'company', 'individual') DEFAULT 'individual',
    company_cif VARCHAR(20) DEFAULT NULL,
    company_name VARCHAR(100) DEFAULT NULL,
    company_address VARCHAR(255) DEFAULT NULL,
    company_city VARCHAR(100) DEFAULT NULL,
    company_postal_code VARCHAR(5) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Taula d'ofertes
CREATE TABLE IF NOT EXISTS offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    message TEXT,
    image VARCHAR(255) DEFAULT 'no-image.png',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Taula de comandes
CREATE TABLE IF NOT EXISTS orders (
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
);

-- Taula de línies de comanda
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Taula de recuperació de clau
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- DADES INICIALS
-- =============================================

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Plantes d\'Interior', 'plantes-interior', 'Plantes perfectes per a decorar la llar'),
('Plantes d\'Exterior', 'plantes-exterior', 'Plantes robustes per a jardins i terrasses'),
('Llavors', 'llavors', 'Llavors de qualitat per al vostre hort'),
('Eines de Jardí', 'eines-jardi', 'Tot el que necessiteu per a treballar el jardí'),
('Testos i Jardineres', 'testos-jardineres', 'Testos i jardineres de tots els mides i materials'),
('Fertilitzants', 'fertilitzants', 'Adobs i fertilitzants per a les vostres plantes');

-- Productes (amb URLs d'imatge de placeholders)
INSERT INTO products (name, description, price, category_id, featured, image, stock) VALUES
('Monstera Deliciosa', 'La planta tropical per excel·lència. Ideal per a salons amb molta llum indirecta. Creix molt i és fàcil de mantenir.', 24.99, 1, 1, 'monstera.jpg', 15),
('Ficus Lyrata (Figuera de Fulla Violí)', 'Planta d\'interior de gran port i fulles espectaculars. Necessita llum abundant i reg moderat.', 39.99, 1, 1, 'ficus-lyrata.jpg', 8),
('Pothos Daurat', 'Planta enfiladissa perfecta per a principiants. Tolera poca llum i regs esporàdics.', 9.99, 1, 0, 'pothos.jpg', 30),
('Calathea Orbifolia', 'Planta ornamental amb fulles ratllades molt decoratives. Necessita humitat i llum indirecta.', 18.50, 1, 0, 'calathea.jpg', 12),
('Lavanda', 'La planta aromàtica mediterrània per excel·lència. Perfecta per a jardins i terrasses assolellades.', 7.99, 2, 1, 'lavanda.jpg', 50),
('Romaní', 'Herba aromàtica i culinària molt resistent. Ideal per a jardins secs i terrasses al sol.', 5.99, 2, 0, 'romani.jpg', 45),
('Rosella', 'Flor silvestre de color vermell intens. Molt fàcil de cultivar i atreu pol·linitzadors.', 6.50, 2, 0, 'rosella.jpg', 35),
('Olivera Nana', 'Varietat compacta de l\'olivera mediterrània. Perfecta per a terrasses i jardins petits.', 29.99, 2, 1, 'olivera.jpg', 10),
('Llavors de Tomàquet Cherry', 'Varietat de tomàquet de mida petita, molt productiva. 50 llavors per sobre.', 3.99, 3, 0, 'llavors-tomaquet.jpg', 100),
('Llavors d\'Enciam Batavia', 'Enciam de fulla verda, cruixent i saborós. Ideal per a horts urbans. 200 llavors.', 2.50, 3, 0, 'llavors-enciam.jpg', 80),
('Llavors de Gira-sol', 'Gira-sol clàssic de tija alta. Perfecte per a jardins i atreure ocells. 30 llavors.', 2.99, 3, 0, 'llavors-girasol.jpg', 60),
('Rastel de Jardí Professional', 'Rastel amb mànec de fusta de 130 cm i 14 pues d\'acer inoxidable. Molt resistent i lleuger.', 22.95, 4, 0, 'rastel.jpg', 20),
('Tisores de Poda INOX', 'Tisores de poda d\'acer inoxidable amb empunyadura ergonòmica. Molt tallen bé i duradores.', 16.99, 4, 0, 'tisores-poda.jpg', 25),
('Pala de Mà Set 3 peces', 'Set de 3 peces: pala de mà, rastell de mà i trasplantador. Mànecs de fusta amb detalls de coure.', 14.50, 4, 0, 'pala-ma.jpg', 30),
('Test Ceràmica Blanc 20cm', 'Test de ceràmica blanc mat, amb forat de drenatge i plat inclòs. Diàmetre 20 cm.', 11.99, 5, 0, 'test-ceramica.jpg', 40),
('Jardinera de Terracota 40cm', 'Jardinera de terracota natural, amb acabat rústic. Dimensions: 40x20x20 cm.', 18.95, 5, 0, 'jardinera-terracota.jpg', 25),
('Test Penjant Macramé', 'Test de ceràmica 15cm amb penjador de macramé fet a mà. Color terracota. Inclou plat.', 15.99, 5, 1, 'test-penjant.jpg', 15),
('Adob Universal Líquid 1L', 'Adob líquid complet per a tota mena de plantes. Ric en N-P-K i microelements. Per a 100 regs.', 8.99, 6, 0, 'adob-liquid.jpg', 50),
('Substrat Universal 20L', 'Substrat de qualitat superior per a plantar i trasplantar. Enriquit amb turba, perlita i compost. Sac 20L.', 12.99, 6, 0, 'substrat.jpg', 35),
('Fertilitzant Orgànic Roses 1kg', 'Fertilitzant granulat d\'origen orgànic especialment formulat per a roses i plantes amb flor.', 10.50, 6, 0, 'fertilitzant-roses.jpg', 30);

-- Usuari administrador (password: Admin1234!)
INSERT INTO users (username, email, password, full_name, user_type) VALUES
('admin', 'admin@jardinsllica.cat', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Web', 'admin');

-- Nota: La contrasenya 'Admin1234!' s'ha de generar amb password_hash('Admin1234!', PASSWORD_DEFAULT)
-- Per a proves usem: password = 'password' (hash de la paraula 'password')

-- Ofertes inicials
INSERT INTO offers (name, message, image, active) VALUES
('Primavera a Lliçà', '20% de descompte en totes les plantes d\'exterior! Aprofita l\'inici de la primavera per renovar el teu jardí.', 'oferta-primavera.jpg', 1),
('Set de Jardineria', 'Compra el rastel + les tisores de poda i emporta\'t el set de pala de mà GRATIS! Oferta per temps limitat.', 'oferta-set.jpg', 1);
