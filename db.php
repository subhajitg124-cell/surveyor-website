<?php
/**
 * Database Connection - SQLite via PDO
 */

define('DB_PATH', __DIR__ . '/database.sqlite');

function getDBConnection() {
    static $pdo = null;
    
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec('PRAGMA foreign_keys=ON');
        
        initializeDatabase($pdo);
        
        return $pdo;
    } catch (Exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

function initializeDatabase($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            phone TEXT NOT NULL,
            location TEXT NOT NULL,
            survey_type TEXT NOT NULL,
            preferred_date TEXT NOT NULL,
            message TEXT,
            status TEXT DEFAULT 'pending' CHECK(status IN ('pending','confirmed','completed','cancelled')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            full_name TEXT NOT NULL,
            is_active INTEGER DEFAULT 1,
            last_login DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS site_data (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            data_key TEXT UNIQUE NOT NULL,
            data_value TEXT,
            data_type TEXT DEFAULT 'text',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Insert default admin user if not exists
    // Username: admin, Password: admin123
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row['cnt'] == 0) {
        $pdo->exec("
            INSERT INTO admin_users (username, password_hash, email, full_name)
            VALUES (
                'admin',
                '\$2y\$10\$D.jjza.bRceY1yA579AOv.62kZyzi6f1lxlj8RtCyQ0sj5g6GriJq',
                'admin@sgsurvey.com',
                'Administrator'
            )
        ");
    }

    // Insert default site data if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM site_data");
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row['cnt'] == 0) {
        $defaults = [
            ['phone_primary', '9475465392', 'text'],
            ['phone_secondary', '8637829746', 'text'],
            ['email', 'swarupanandaghosh2@gmail.com', 'text'],
            ['location', 'Kochkunda, Shitla, Bankura', 'text'],
            ['charge_land_survey', '5000', 'number'],
            ['charge_digital_survey', '8000', 'number'],
            ['charge_autocad_sketch', '3000', 'number'],
            ['charge_laser_survey', '10000', 'number'],
        ];
        $stmt = $pdo->prepare("INSERT INTO site_data (data_key, data_value, data_type) VALUES (?, ?, ?)");
        foreach ($defaults as $d) {
            $stmt->execute($d);
        }
    }
}
