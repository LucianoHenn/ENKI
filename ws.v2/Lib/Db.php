<?php

namespace WsV2\Lib;


class Db
{
    protected $db_config;

    protected $pdo;

    public function __construct($db_config)
    {
        $this->db_config = $db_config;
    }

    protected function connect()
    {
        if(is_null($this->pdo)) {
            $driver = $this->db_config['driver'];
            $host = $this->db_config['host'];
            $database = $this->db_config['database'];
            $username = $this->db_config['username'];
            $password = $this->db_config['password'];
            $charset = $this->db_config['charset'];
            $this->pdo = new \PDO(
                "{$driver}:host={$host};dbname={$database}",
                $username, $password,
                array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}"));
        }
    }

    public function execute($query, $params = []) 
    {
        $this->connect();
        return $this->pdo->prepare($query)->execute($params);
    }

    public function query($query, $params = []) 
    {
        $this->connect();
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}