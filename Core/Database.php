<?php

namespace App\Core;

use mysqli;

/**
 * Clase Database - Patrón Singleton para conexión a base de datos
 */
class Database
{
    private static $instance = null;
    private $connection = null;

    private function __construct()
    {
        $this->connect();
    }

    /**
     * Obtener instancia única de la base de datos
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Conectar a la base de datos
     */
    private function connect()
    {
        $hostname = getenv('DB_HOST');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASS');
        $schema = getenv('DB_SCHEMA');
        $port = getenv('DB_PORT');

        // Establecer puerto por defecto si no está definido
        $port = ($port !== false && $port !== '') ? (int) $port : 3306;

        // Conexión mysqli con puerto personalizado
        $this->connection = new mysqli($hostname, $username, $password, $schema, $port);

        if ($this->connection->connect_error) {
            die("Error de conexión a la base de datos: " . $this->connection->connect_error);
        }

        // Configurar charset
        $this->connection->set_charset("utf8");
    }

    /**
     * Obtener la conexión mysqli
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Ejecutar query y devolver resultado
     */
    public function query($sql)
    {
        $result = $this->connection->query($sql);
        if (!$result) {
            throw new \Exception("Error en query: " . $this->connection->error);
        }
        return $result;
    }

    /**
     * Prepared statement
     */
    public function prepare($sql)
    {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new \Exception("Error en prepare: " . $this->connection->error);
        }
        return $stmt;
    }

    /**
     * Obtener último ID insertado
     */
    public function insertId()
    {
        return $this->connection->insert_id;
    }

    /**
     * Escapar string
     */
    public function escape($value)
    {
        return $this->connection->real_escape_string($value);
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction()
    {
        $this->connection->autocommit(false);
        return $this->connection->begin_transaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit()
    {
        $result = $this->connection->commit();
        $this->connection->autocommit(true);
        return $result;
    }

    /**
     * Revertir transacción
     */
    public function rollback()
    {
        $result = $this->connection->rollback();
        $this->connection->autocommit(true);
        return $result;
    }

    /**
     * Ejecutar múltiples operaciones en transacción
     */
    public function transaction(callable $operations)
    {
        try {
            $this->beginTransaction();
            $result = $operations($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Cerrar conexión
     */
    public function close()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    // Prevenir clonación
    private function __clone() {}

    // Prevenir deserialización
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}