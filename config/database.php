<?php
/**
 * ASTROGAMES - Database Connection & Helper Functions
 * 
 * Konfigurasi koneksi MySQL / MariaDB menggunakan PDO.
 * Disesuaikan untuk lingkungan Laragon (Host: 127.0.0.1, User: root, Pass: empty).
 */

declare(strict_types=1);

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'u681331588_astro');
define('DB_USER', 'u681331588_astro');
define('DB_PASS', '$@BuhbkE|2');
define('DB_CHARSET', 'utf8mb4');

/**
 * Mendapatkan koneksi PDO (Singleton pattern)
 * Auto-creates database 'astrogames_db' if it doesn't exist yet!
 * 
 * @return PDO|null
 */
function getDBConnection(): ?PDO {
    static $pdo = null;

    if ($pdo === null) {
        $options = [
            PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND       => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            // Try connecting to specific DB
            $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=%s", DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If DB doesn't exist, connect to MySQL host and create DB + import SQL!
            try {
                $hostDsn = sprintf("mysql:host=%s;port=%s;charset=%s", DB_HOST, DB_PORT, DB_CHARSET);
                $hostPdo = new PDO($hostDsn, DB_USER, DB_PASS, $options);
                $hostPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

                // Now connect to created DB
                $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=%s", DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

                // Auto import schema if SQL file exists
                $sqlPath = __DIR__ . '/../astrogames_database.sql';
                if (file_exists($sqlPath)) {
                    $sql = file_get_contents($sqlPath);
                    $pdo->exec($sql);
                }
            } catch (PDOException $ex) {
                error_log("ASTROGAMES Database Error: " . $ex->getMessage());
                return null;
            }
        }
    }

    return $pdo;
}

/**
 * Helper prepared statement query serbaguna
 */
function dbQuery(string $sql, array $params = []): ?PDOStatement {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("DB Query Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Helper to execute a query and return boolean success (for INSERT, UPDATE, DELETE)
 */
function dbExecute(string $sql, array $params = []): bool {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("DB Execute Error: " . $e->getMessage());
        throw $e; // Rethrow so try/catch in admin/index.php can catch it for rollback
    }
}

function dbBegin() {
    $pdo = getDBConnection();
    if ($pdo && !$pdo->inTransaction()) {
        $pdo->beginTransaction();
    }
}

function dbCommit() {
    $pdo = getDBConnection();
    if ($pdo && $pdo->inTransaction()) {
        $pdo->commit();
    }
}

function dbRollback() {
    $pdo = getDBConnection();
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

/**
 * Helper fetch single row
 */
function dbFetchOne(string $sql, array $params = []): array|false {
    $stmt = dbQuery($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

/**
 * Helper fetch all rows
 */
function dbFetchAll(string $sql, array $params = []): array {
    $stmt = dbQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

/**
 * Helper CSRF protection token
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
