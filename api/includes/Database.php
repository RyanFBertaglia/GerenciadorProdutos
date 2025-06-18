<?php
namespace Api\Includes;

class Database {
    private static $instance = null;
    private $pdo;

    private const HOST = 'localhost';
    private const DBNAME = 'loja';
    private const USER = 'root';
    private const PASS = '';

    private function __construct() {
        try {
            $this->pdo = new \PDO(
                "mysql:host=" . self::HOST . ";dbname=" . self::DBNAME . ";charset=utf8",
                self::USER,
                self::PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (\PDOException $e) {
            error_log('Erro na conexão: ' . $e->getMessage());
            throw new \RuntimeException('Falha na conexão com o banco de dados');
        }
    }

    // Singleton
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    private function __clone() {}
    private function __wakeup() {}
}