<?php
/**
 * db_conexion.php
 * Conexión centralizada y segura a MariaDB.
 * Soporta arquitectura HA y previene filtraciones.
 */

function getDbConnection() {
    // detecta ip local servidor
    // ip local del cluster
    // compatible proxy tcp balanceador
    // configurado subred segura
    $host = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
    $user = 'ytronadm';
    $pass = 'ytronadm';
    $db   = 'ytronhosting';

    // activa reporte excepciones bd
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conexion = mysqli_connect($host, $user, $pass, $db);
        mysqli_set_charset($conexion, "utf8mb4");
        return $conexion;
    } catch (mysqli_sql_exception $e) {
        // registra log error silencioso
        error_log("Database connection error on node {$host}: " . $e->getMessage());
        
        // muestra error generico cliente
        http_response_code(503); // Service Unavailable
        die("
            <div style='font-family: sans-serif; text-align: center; margin-top: 100px; padding: 20px; color: #333;'>
                <h1 style='color: #d9534f;'>Servicio Temporalmente No Disponible</h1>
                <p>Nuestros ingenieros de DevOps están trabajando para restablecer el acceso a la base de datos.</p>
                <p style='color: #777; font-size: 12px;'>Error Interno (Ytron Hosting HA-Cluster)</p>
            </div>
        ");
    }
}
?>
