<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    /**
     * Obtiene una instancia singleton de conexión PDO a MySQL
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host = '127.0.0.1';
            $port = '3306';
            $dbName = 'login';
            $username = 'root';
            $password = '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connection = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error de conexión a la base de datos MySQL: ' . $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        return self::$connection;
    }
}
