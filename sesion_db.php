<?php
/**
 * Manejador de sesiones compartidas mediante MariaDB.
 * Incluir ANTES de session_start().
 */

// conexion dedicada sesiones
// previene excepciones modo estricto
$sesion_host = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
try {
    $__sesion_conn = mysqli_connect($sesion_host, "ytronadm", "ytronadm", "ytronhosting");
} catch (Exception $e) {
    $__sesion_conn = null; // usa archivos por defecto
}

if ($__sesion_conn) {

    // crea tabla de sesiones
    try {
        mysqli_query($__sesion_conn,
            "CREATE TABLE IF NOT EXISTS sesiones_php (
                id VARCHAR(128) NOT NULL PRIMARY KEY,
                datos TEXT NOT NULL,
                ultimo_acceso INT NOT NULL,
                INDEX idx_ultimo_acceso (ultimo_acceso)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (Exception $e) {
        // ignora si ya existe
    }

    class DbSessionHandler implements SessionHandlerInterface
    {
        private $host;
        private $user = "ytronadm";
        private $pass = "ytronadm";
        private $db = "ytronhosting";
        private $conn;

        public function __construct() { 
            $this->host = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
            $this->connect();
        }
        
        private function connect() {
            try {
                // fuerza nueva conexion limpia
                $this->conn = @mysqli_connect($this->host, $this->user, $this->pass, $this->db);
            } catch (Exception $e) {
                $this->conn = null;
            }
        }
        
        private function ensureConnection() {
            if (!$this->conn || !@mysqli_ping($this->conn)) {
                $this->connect();
            }
            return $this->conn !== null;
        }

        public function open(string $path, string $name): bool
        {
            return $this->ensureConnection();
        }

        public function close(): bool { return true; }

        public function read(string $id): string|false
        {
            if (!$this->ensureConnection()) return '';
            try {
                $stmt = mysqli_prepare($this->conn, "SELECT datos FROM sesiones_php WHERE id = ?");
                if (!$stmt) return '';
                mysqli_stmt_bind_param($stmt, "s", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                return $row ? $row['datos'] : '';
            } catch (Exception $e) {
                return '';
            }
        }

        public function write(string $id, string $data): bool
        {
            if (!$this->ensureConnection()) return false;
            try {
                $timestamp = time();
                $stmt = mysqli_prepare($this->conn,
                    "REPLACE INTO sesiones_php (id, datos, ultimo_acceso) VALUES (?, ?, ?)"
                );
                if (!$stmt) return false;
                mysqli_stmt_bind_param($stmt, "ssi", $id, $data, $timestamp);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                return $ok;
            } catch (Exception $e) {
                return false;
            }
        }

        public function destroy(string $id): bool
        {
            if (!$this->conn) return false;
            try {
                $stmt = mysqli_prepare($this->conn, "DELETE FROM sesiones_php WHERE id = ?");
                if (!$stmt) return false;
                mysqli_stmt_bind_param($stmt, "s", $id);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                return $ok;
            } catch (Exception $e) {
                return false;
            }
        }

        public function gc(int $max_lifetime): int|false
        {
            if (!$this->conn) return false;
            try {
                $limite = time() - $max_lifetime;
                $stmt = mysqli_prepare($this->conn, "DELETE FROM sesiones_php WHERE ultimo_acceso < ?");
                if (!$stmt) return false;
                mysqli_stmt_bind_param($stmt, "i", $limite);
                mysqli_stmt_execute($stmt);
                $borradas = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);
                return $borradas;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    $handler = new DbSessionHandler();
    session_set_save_handler($handler, true);
    register_shutdown_function('session_write_close');
}
