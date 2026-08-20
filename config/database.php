<?php
/**
 * Desire Travel - Secure Database Connection Handler (PDO)
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = DB_HOST;
            $db   = DB_NAME;
            $user = DB_USER;
            $pass = DB_PASS;
            $charset = 'utf8mb4';

            $portsToTry = [DB_PORT, 3306, 3307];
            $lastException = null;
            $connected = false;

            foreach ($portsToTry as $port) {
                try {
                    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
                    $options = [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ];
                    self::$instance = new PDO($dsn, $user, $pass, $options);
                    $connected = true;
                    break;
                } catch (PDOException $e) {
                    $lastException = $e;
                    // If database doesn't exist yet, try creating it
                    if ($e->getCode() == 1049) {
                        try {
                            $rootDsn = "mysql:host={$host};port={$port};charset={$charset}";
                            $rootPdo = new PDO($rootDsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                            
                            $sqlFile = BASE_DIR . '/database/database.sql';
                            if (file_exists($sqlFile)) {
                                $sqlContent = file_get_contents($sqlFile);
                                $rootPdo->exec("USE `{$db}`; " . $sqlContent);
                            }
                            
                            self::$instance = new PDO("mysql:host={$host};port={$port};dbname={$db};charset={$charset}", $user, $pass, $options);
                            $connected = true;
                            break;
                        } catch (PDOException $createEx) {
                            $lastException = $createEx;
                        }
                    }
                }
            }

            if (!$connected) {
                die("<div style='font-family:sans-serif;padding:30px;background:#fff1f2;color:#9f1239;border-left:6px solid #e11d48;margin:40px auto;max-width:700px;border-radius:8px;'>
                    <h2 style='margin-top:0;'>⚠️ Desire Travel Database Connection Error</h2>
                    <p>Could not connect to MySQL server on host <strong>{$host}</strong> (tested ports: " . implode(', ', $portsToTry) . ").</p>
                    <p><strong>Error Message:</strong> " . htmlspecialchars($lastException ? $lastException->getMessage() : 'Unknown error') . "</p>
                    <p>Please ensure MySQL is running in XAMPP or update <code>config/config.php</code>.</p>
                </div>");
            }
        }

        return self::$instance;
    }
}

// Global PDO reference
$pdo = Database::getConnection();
