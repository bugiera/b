<?php

class TextsGateway {
    
    private $db = null;
    
    private $logs = null;
    private $logsSection = null;
    
    public $errors = array();

    public function __construct($db, $logs) {

        $this->db = $db;

        $this->logs = $logs;
        $this->logsSection = basename(__FILE__);
        
    }
    
    
    
}

?>