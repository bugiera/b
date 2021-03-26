<?php

class Database {

    private $host = null;
    private $db_name = null;
    private $username = null;
    private $password = null;
    
    private $conn = null;
    
    private $error = false;

    public function __construct($host, $db_name, $username, $password) {

        $this->host = $host;
        $this->db_name = $db_name;
        $this->username = $username;
        $this->password = $password;

        try {
            
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_PERSISTENT, true);      // https://www.php.net/manual/en/pdo.connections.php -> Example #4 Persistent connections
            
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            $this->conn->exec("set names utf8");
//            $this->conn->exec("set names utf8;set time_zone = 'Europe/Warsaw'");
        } catch (\PDOException $e) {
            
            $this->error = $e->getMessage();
            
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn = null;
    }

    public function hasError() {
        return ($this->error) ? true : false;
    }

    public function getError() {
        return $this->error;
    }

}

?>