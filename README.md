# 🌿 Jardins de Lliçà — Botiga en línia PHP

Projecte de botiga en línia per al comerç **Jardins de Lliçà** (Lliçà d'Amunt, Vallès Oriental), especialitzat en plantes, llavors i accessoris de jardineria.

## Requisits

- PHP 8.0 o superior
- MySQL 5.7 o superior (o MariaDB 10.4+)
- Servidor web: Apache (XAMPP/WAMP/LAMP) o Nginx
- Extensions PHP: `pdo_mysql`, `mbstring`, `json`, `fileinfo`

## Instal·lació

### 1. Col·loca el projecte
Copia la carpeta `PHP_WEB_EI/` dins de `htdocs/` (XAMPP) o `www/` (WAMP).

### 2. Configura la base de dades
Edita `config/database.php` amb les teves credencials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // El teu usuari de MySQL
define('DB_PASS', '');           // La teva contrasenya
define('DB_NAME', 'jardins_llica');
define('SITE_URL', 'http://localhost/PHP_WEB_EI'); // URL del projecte
```

### 3. Executa la configuració inicial
Obre al navegador: `http://localhost/PHP_WEB_EI/setup.php`

Això crearà automàticament:
- La base de dades `jardins_llica`
- Totes les taules necessàries
- 6 categories de productes
- 20 productes de mostra
- 2 ofertes de mostra
- L'usuari administrador

### 4. Credencials d'administrador
- **Usuari:** `admin`
- **Clau:** `Admin1234!`

> ⚠️ **Seguretat:** Elimina `setup.php` després de la configuració!

## Estructura del projecte

```
PHP_WEB_EI/
├── index.php               # Pàgina d'inici
├── login.php               # Inici de sessió
├── logout.php              # Tancament de sessió
├── register.php            # Registre d'usuaris
├── account.php             # Gestió del compte
├── cart.php                # Cistella de la compra
├── checkout.php            # Procés de compra
├── search.php              # Cerca de productes
├── section.php             # Pàgines per secció/categoria
├── recover-password.php    # Recuperació de clau
├── contact.php             # Pàgina de contacte
├── cookies-policy.php      # Política de cookies i RGPD
├── setup.php               # Configuració inicial (eliminar!)
├── config/
│   └── database.php        # Configuració de la BD i correu
├── includes/
│   ├── header.php          # Capçalera i navegació
│   ├── footer.php          # Peu de pàgina
│   ├── functions.php       # Funcions auxiliars
│   └── email.php           # Funcions d'enviament de correu
├── admin/
│   ├── index.php           # Dashboard d'administració
│   ├── products.php        # Gestió de productes
│   ├── users.php           # Gestió d'usuaris
│   └── offers.php          # Gestió d'ofertes
├── css/
│   └── style.css           # Estils personalitzats
├── uploads/
│   ├── products/           # Imatges de productes
│   └── offers/             # Imatges d'ofertes
└── database.sql            # Script SQL de referència
```

## Funcionalitats implementades

| # | Funcionalitat | Estat |
|---|--------------|-------|
| 1 | Capçalera amb navegació dinàmica | ✅ |
| 2 | Peu de pàgina amb autors i copyright | ✅ |
| 3 | Pàgina d'inici (ofertes + destacats) | ✅ |
| 4 | Seccions i cerca de productes | ✅ |
| 5 | Registre d'usuaris | ✅ |
| 6 | Login / Logout amb sessions | ✅ |
| 7 | Cistella amb cookies | ✅ |
| 8 | Pàgina de compra amb validació | ✅ |
| 9 | Gestió del compte d'usuari | ✅ |
| 10 | Panell d'administració | ✅ |
| 11 | Enviament de correus (mail()) | ✅ |
| 12 | Validació de DNI (algoritme oficial) | ✅ |
| 13 | Recuperació de clau per correu | ✅ |
| 14 | Pàgina de contacte amb Google Maps | ✅ |
| 15 | Política de cookies i protecció de dades | ✅ |
| 16 | Laravel | ❌ (PHP natiu) |
| 17 | CSS + Bootstrap 5 | ✅ |

## Notes tècniques

- **Sessió:** Login amb `$_SESSION` (PHP sessions)
- **Cistella:** `$_COOKIE['cart']` amb JSON (7 dies de durada)
- **Contrasenya:** Hash amb `password_hash()` + `password_verify()`
- **SQL:** Consultes preparades PDO (prevenció SQL injection)
- **XSS:** Escapament amb `htmlspecialchars()` a totes les sortides
- **DNI:** Validació amb algoritme oficial (lletra de control % 23)
- **Correu:** `mail()` nativa de PHP (cal servidor SMTP configurat per a entorn de producció)
- **CSS:** Bootstrap 5.3.2 + CSS personalitzat

## Autors

Alumnes de l'INS Lliçà d'Amunt — Curs 2025-2026
