<?php

class LogsController {
    
    private $db;
    private $requestMethod;
    private $uriParts;
    private $fromJwt;
    private $utilities;
    
    private $logs = array();
    
    private $logsGateway;
    private $logsSection;
    
    private $datetime;
    private $errors = array();
    
    private $validation = array();
    private $recaptcha = null;
    
    private $txt;
    
    private $userId;
    private $logId;
    
    public function __construct($db, $logs = false, $txt = false, $requestMethod, $uriParts, $fromJwt = false) {

        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->uriParts = $uriParts;
        $this->fromJwt = $fromJwt;
        $this->utilities = new Utilities();

        $this->txt = $txt;

        $this->logsGateway = new LogsGateway($db);
        $this->logsSection = basename(__FILE__);

        $this->datetime = new DateTime();
        
    }
    
    public function processRequest() {
        
        $this->userId = null;
        if(isset($this->fromJwt->data->user_id)) {
            $this->userId = (int) $this->fromJwt->data->user_id;
        }
        
        $this->logId = null;
        if (isset($this->uriParts[2])) {
            $this->logId = (int) $this->uriParts[2];
        }
        
        $mode = null;
        if (isset($this->uriParts[3])) {
            $mode = $this->uriParts[3];
        }

        switch ($this->requestMethod) {
            case 'GET':

//                    if ($mode == null) {
//                        $response = $this->getLog($this->logId);
//                    } else {
                        $response = $this->getAllLogs();
//                    }

                break;
//            case 'POST':
//
//                $response = $this->createLogFromRequest();
//
//                break;
//            case 'PUT':
//
//                if ((isset($this->fromJwt->data->role)) && ($this->fromJwt->data->role == 'admin')) {
//                    $response = $this->updateLogFromRequest($this->logId);
//                } else {
//
//                    $response['code'] = 403;
//                    $response['section'] = $this->logsSection . '-processRequest';
//                    $response['json'] = json_encode([
//                        'logId' => $this->logId
//                    ]);
//                }
//
//                break;
//            case 'DELETE':
//
//                if ((isset($this->fromJwt->data->role)) && ($this->fromJwt->data->role == 'admin')) {
//                    $response = $this->deleteLog($this->logId);
//                } else {
//
//                    $response['code'] = 403;
//                    $response['section'] = $this->logsSection . '-processRequest';
//                    $response['json'] = json_encode([
//                        'logId' => $this->logId
//                    ]);
//                }
//
//                break;
            default:

                $response['code'] = 404;
                $response['section'] = $this->logsSection . '-processRequest';
                $response['json'] = json_encode([
                    'logId' => $this->logId
                ]);

                break;
        }

        if ($response) {
            if (USE_LOGS)
                $this->saveLogs();
            if (USE_OB_GZHANDLER) {
                ob_start("ob_gzhandler");
                echo $this->utilities->show_response($response);
                ob_end_flush();
            } else {
                echo $this->utilities->show_response($response);
            }
        }
    }
    
//    private function getLog($log_id = null) {
//        
//    }
    
    private function getAllLogs() {
        
        $result = $this->logsGateway->findAll();
        $results_returned = array();

        foreach ($result as $r) {
            $results_returned[] = array(
                'id' => (int) $r['log_id'],
                'create_date' => $r['create_date'],
                'code' => (int) $r['code'],
                'request_method' => $r['request_method'],
                'uri_parts' => $r['uri_parts'],
                'section' => $r['section'],
                'message' => $r['message'],
                'json' => $r['json'],
            );
        }

        $response['code'] = 200;
        $response['section'] = $this->logsSection . '-getAllLogs';
        $response['body'] = $results_returned;
        $response['message'] = sprintf($this->txt->t('Logs found: %s'), count($results_returned));

        if ((USE_LOGS) && (SAVE_LOGS_WITH_CODE_200))
            $this->log->add(array(
                'user_id' => $this->userId,
                'response' => $response
            ));

        return $response;
        
    }
    
    public function add($input) {
        
        $response = (isset($input['response']) && (is_array($input['response']))) ? $this->utilities->show_response($input['response'], false, true) : array();
        
        if (isset($this->fromJwt->data->user_id))
            $input['user_id'] = (int) $this->fromJwt->data->user_id;
        
        $input['type'] = (isset($input['response']['type'])) ? (int) $input['response']['type'] : null;
        $input['section'] = (isset($input['response']['section'])) ? (string) $input['response']['section'] : null;
        
        $input['code'] = (isset($response['code'])) ? (int) $response['code'] : null;
        $input['request_method'] = (string) $this->requestMethod;
        $input['uri_parts'] = (is_array($this->uriParts)) ? (string) implode('/', $this->uriParts) : (string) $this->uriParts;
        
        $input['message'] = (isset($response['message'])) ? (string) $response['message'] : null;
        
        $json = array();
        if ((isset($response['json'])) && (!empty($response['json'])) && ($this->utilities->is_valid_json(json_encode($response['json'])))) {
            $json['json'] = json_decode($response['json'], true);
        }
        if ((isset($response['recaptcha'])) && (!empty($response['recaptcha']))) {
            $json['recaptcha'] = $response['recaptcha'];
        }
        if ((isset($response['errors'])) && (!empty($response['errors'])) && (is_array($response['errors']))) {
            $json['errors'] = $response['errors'];
        }
        $input['json'] = json_encode($json);
        
        $this->logs[] = $input;
        
        return true;
        
    }
    
    public function saveLogs() {

        if ($inserted = $this->logsGateway->insert($this->logs)) {

            $count = (int) (is_array($inserted)) ? count($inserted) : 0;

            $response['code'] = 201;
            $response['section'] = $this->logsSection . '-saveLogs';
            $response['json'] = json_encode([
//                'inserted' => $inserted,
                'inserted_count' => $count
            ]);
            $response['message'] = sprintf(($this->txt) ? $this->txt->t('the log has been created (%s operations were performed)') : 'the log has been created (%s operations were performed)', $count);

            return $response;
            
        } else {

            $response['code'] = 500;
            $response['section'] = $this->logsSection . '-saveLogs';
            $response['message'] = ($this->txt) ? $this->txt->t('errors occurred while trying to create the log') : 'errors occurred while trying to create the log';
            $response['json'] = json_encode([
                'logs' => $this->logs
            ]);
            $response['errors'] = array_merge($this->errors, $this->logsGateway->errors);

            return $response;
            
        }
        
    }
    
//    private function createLogFromRequest() {
//        
//    }

//    private function updateLogFromRequest($log_id = null) {
//        
//    }

//    private function deleteLog($log_id = null) {
//        
//    }
    
//    private function validateLog($input) {
//        
//    }
    
}

?>