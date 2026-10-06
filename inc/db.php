<?php
/**
 * Atlas Shop Database Connection (PDO)
 */

require_once __DIR__ . '/config.php';

function get_db() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // If MySQL connection fails due to missing DB, try auto-create DB if permitted
        if (strpos($e->getMessage(), 'Unknown database') !== false || $e->getCode() == 1049) {
            try {
                $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
                $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                return $pdo;
            } catch (Exception $dbEx) {}
        }

        // SQLite fallback for local preview environment
        try {
            $sqlitePath = PATH_ROOT . '/data/atlas_shop.sqlite';
            $isNewDb = !file_exists($sqlitePath);
            $pdo = new PDO("sqlite:" . $sqlitePath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            if ($isNewDb) {
                init_sqlite_db($pdo);
            }
            return $pdo;
        } catch (PDOException $sqliteEx) {
            error_log("Database Connection Error: " . $sqliteEx->getMessage());
            return null;
        }
    }
}

function init_sqlite_db($pdo) {
    $sqlFile = PATH_ROOT . '/data/schema.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $sql = str_replace('SET FOREIGN_KEY_CHECKS = 0;', 'PRAGMA foreign_keys = OFF;', $sql);
        $sql = str_replace('SET FOREIGN_KEY_CHECKS = 1;', 'PRAGMA foreign_keys = ON;', $sql);
        $sql = preg_replace('/INT AUTO_INCREMENT PRIMARY KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
        $sql = preg_replace('/ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci/i', '', $sql);
        $sql = preg_replace('/,\s*FOREIGN KEY\s*\([^)]+\)\s*REFERENCES\s*\w+\([^)]+\)(\s*ON\s+(DELETE|UPDATE)\s+\w+)*/i', '', $sql);
        $sql = preg_replace('/ENUM\([^)]+\)/i', 'VARCHAR(50)', $sql);
        $sql = str_replace('ON UPDATE CURRENT_TIMESTAMP', '', $sql);

        $queries = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($queries as $query) {
            if (!empty($query)) {
                try {
                    $pdo->exec($query);
                } catch (Exception $e) {}
            }
        }
    }
}

$db = get_db();
