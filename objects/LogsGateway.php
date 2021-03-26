<?php

class LogsGateway {
    
    private $db = null;
    public $errors = array();
    
    public function __construct($db) {

        $this->db = $db;
        
    }
    
//    public function count($mode) {
//        
//    }
    
    public function findAll() {
        
        $statement = "SELECT "
                . "log_id,"
                . "user_id,"
                . "create_date,"
                . "type,"
                . "code,"
                . "request_method,"
                . "uri_parts,"
                . "section,"
                . "message,"
                . "json "
                . "FROM logs "
                . "ORDER BY create_date DESC;";
        
        try {
            
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $result;
            
        } catch (PDOException $ex) {
            $this->errors[] = $ex->getMessage();
            return false;
        }
        return false;
    }
    
    public function insert(Array $logs) {
        try {
            
            $inserted_id = array();

            $this->db->beginTransaction();

            foreach ($logs as $input) {
                $statement = $this->db->prepare("INSERT INTO logs ("
                        . "type,"
                        . "section,"
                        . "message,"
                        . "json,"
                        . "code,"
                        . "user_id,"
                        . "request_method,"
                        . "uri_parts"
                        . ") VALUES ("
                        . ":type,"
                        . ":section,"
                        . ":message,"
                        . ":json,"
                        . ":code,"
                        . ":user_id,"
                        . ":request_method,"
                        . ":uri_parts"
                        . ");");

                $user_id = (isset($input['user_id'])) ? (int) $input['user_id'] : null;

                $statement->bindParam(':type', $input['type'], PDO::PARAM_INT);
                $statement->bindParam(':code', $input['code'], PDO::PARAM_INT);
                $statement->bindParam(':section', $input['section'], PDO::PARAM_STR);
                $statement->bindParam(':message', $input['message'], PDO::PARAM_STR);
                $statement->bindParam(':json', $input['json'], PDO::PARAM_STR);
                $statement->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $statement->bindParam(':request_method', $input['request_method'], PDO::PARAM_STR);
                $statement->bindParam(':uri_parts', $input['uri_parts'], PDO::PARAM_STR);

                $statement->execute();
                $last_insert_id = $this->db->lastInsertId();

                $inserted_id[] = $last_insert_id;
            }

            $this->db->commit();

            return $inserted_id;
            
        } catch (PDOException $ex) {
            $this->db->rollback();
            $this->errors[] = $ex->getMessage();
            return false;
        }
        return false;
    }
    
//    public function find($log_id) {
//        
//    }
//    
//    public function update($log_id,Array $input) {
//        
//    }
//    
//    public function delete($log_id) {
//        
//    }
    
}

?>