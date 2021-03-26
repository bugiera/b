<?php

class TextsController {
    
    private $db;
    private $logs;
    private $requestMethod;
    private $uriParts;
    private $fromJwt;
    
    private $utilities;
    private $lg;
    private $variables = array();
    private $textsGateway;
    
    private $logsSection;
    private $errors = array();
    
    private $validation = array();
    private $recaptcha = null;
    
    private $userId;

    public function __construct($db, $logs, $requestMethod, $uriParts, $fromJwt = false) {

        $this->db = $db;
        $this->logs = $logs;
        $this->requestMethod = $requestMethod;
        $this->uriParts = $uriParts;
        $this->fromJwt = $fromJwt;
        
        $this->utilities = new Utilities();

        $this->textsGateway = new TextsGateway($db, $this->logs);
        
        $this->setLanguage('en_en');

        $this->logsSection = basename(__FILE__);

        $this->userId = null;
        if (isset($this->fromJwt->data->user_id)) {
            $this->userId = (int) $this->fromJwt->data->user_id;
        }
        
    }
    
    public function setLanguage($language = 'pl') {
        switch (strtolower($language)) {
            case 'pl_pl':
            case 'pl':
                $this->lg = 'pl_pl';
                break;
            case 'en_en':
            case 'en':
            default:
                $this->lg = 'en_en';
                break;
        }
        return true;
    }
    
    public function getLanguage() {
        return $this->lg;
    }
    
    public function setVariables($variables) {
        $this->variables = $variables;
    }

    public function getVariables() {
        return $this->variables;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    
    
    public function t($string) {
        
        try {
            
            // automatic adding of non-existent content to the database and return language version

//            $key = md5($string);
//            $results = $this->textsGateway->find($key, $this->userId);
//            if (count($results) > 0) {
//                
//                
//                
//            } else {
//                
//                
//                
//            }

            $txt = $string;

            // Trim unnecessary whitespace (leave line breaks)
            $str = preg_replace("/(?:^((\pZ)+|((?!\R)\pC)+)(?1)*)|((?1)$)|(?:((?2)+|(?3)+)(?=(?2)|(?3)))/um", '', $txt);

            // Convert remaining whitespace to regular spaces (leave line breaks)
            $str = preg_replace("/(\pZ+)|((?!\R)\pC)/u", ' ', $str);

            // Trim line breaks
            $str = preg_replace("/(^\R+)|(\R+$)|(\R(?=\R{2}))/u", '', $str);

            // Sanitize for safe printing between html tags
            $str = htmlspecialchars($str, ENT_HTML5, 'UTF-8');

            $txt = mb_convert_encoding($str,'UTF-8');
        
        
        } catch (Exception $ex) {
            
            $this->errors[] = $ex->getMessage();
            
        }
        
        return ((isset($txt)) && ($txt !== null)) ? $txt : '#[no-text]';
        
    }
    
}

?>