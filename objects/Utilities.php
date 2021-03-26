<?php

Class Utilities {
    
    public $errors = array();
    
    public function is_valid_json($json) {
        return (json_decode($json, true) == NULL) ? FALSE : TRUE;
    }
    
    public function show_response($response, $show_as_json_format = true, $include_input_json = false) {

        $code = (isset($response['code'])) ? (int) $response['code'] : 500;
        $status = (($code == 200) || ($code == 201)) ? 'ok' : 'error';
        
        switch ($code) {
            case 200:
                $status_code_header = 'HTTP/1.1 200 OK';
                $message = 'Ok!';
                break;
            case 201:
                $status_code_header = 'HTTP/1.1 201 Created';
                $message = 'Created!';
                break;
            case 401:
                $status_code_header = 'HTTP/1.1 401 Unauthorized';
                $message = 'Unauthorized!';
                break;
            case 403:
                $status_code_header = 'HTTP/1.1 403 Forbidden';
                $message = 'Forbidden!';
                break;
            case 404:
                $status_code_header = 'HTTP/1.1 404 Not Found';
                $message = 'Not Found!';
                break;
            case 406:
                $status_code_header = 'HTTP/1.1 406 Not Acceptable';
                $message = 'Not Acceptable!';
            case 422:
            default:
                $status_code_header = 'HTTP/1.1 422 Unprocessable Entity';
                $message = 'Unprocessable Entity!';
                break;
            case 500:
                $status_code_header = 'HTTP/1.1 500 Error';
                $message = 'Error!';
                break;
        }
        
        $message = ((isset($response['message'])) && (!empty($response['message']))) ? (string) $response['message'] : (string) $message;
        
        $errors = ((isset($response['errors'])) && (!empty($response['errors'])) && (is_array($response['errors']))) ? (array) $response['errors'] : array();
        
        $section = (isset($response['section'])) ? (string) $response['section'] : null;
        
        $date = new DateTime();
        $timestamp = $date->getTimestamp();

        $gzcompress = ((isset($response['gzcompress'])) && ($response['gzcompress'] == true)) ? true : false;

        $data = (isset($response['body'])) ? $response['body'] : array();

        $json = ((isset($response['json'])) && ($this->is_valid_json($response['json']))) ? $response['json'] : null;

        $header_msg = ((isset($response['header_msg']))&&(!empty($response['header_msg']))&&($response['header_msg'] == true)) ? $status_code_header . ' - ' . $message : $status_code_header;
        
        header($header_msg);
        
        $array = array(
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'errors' => $errors,
            'date' => $date,
            'timestamp' => $timestamp,
            'gzcompress' => $gzcompress,
            'data' => $data,
        );

        if ($include_input_json) {
            $json = ($json != null) ? json_decode($json,true) : array();
            $array['json'] = json_encode($json);
        }
        
        return ($show_as_json_format) ? json_encode($array, JSON_PRETTY_PRINT) : $array;
        
    }
    
    public function authenticate() {
        
        // \Firebase\JWT (JSON Web Tokens) stuff
        
        return false;
        
    }
    
}

?>