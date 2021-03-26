<?php

class PostsController {
    
    private $db;
    private $requestMethod;
    private $uriParts;
    private $fromJwt;
    private $utilities;
    
    private $logs;
    private $logsSection;
    
    private $datetime;
    private $errors = array();
    
    private $txt;
    
    private $postsGateway;
    
    private $postId;
    private $userId;
    
    public function __construct($db, $logs, $txt, $requestMethod, $uriParts, $fromJwt = false) {
        
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->uriParts = $uriParts;
        $this->fromJwt = $fromJwt;
        $this->logs = $logs;
        $this->txt = $txt;
        $this->utilities = new Utilities();
        
        $this->postsGateway = new PostsGateway($db, $this->logs);
        
        $this->logsSection = basename(__FILE__);
        
        $this->datetime = new DateTime();
        
    }
    
    public function processRequest() {
        
        $this->userId = null;
        if (isset($this->fromJwt->data->user_id)) {
            $this->userId = (int) $this->fromJwt->data->user_id;
        }

        $this->postId = null;
        if (isset($this->uriParts[2])) {
            $this->postId = $this->uriParts[2];
        }
        
        switch ($this->requestMethod) {
            case 'GET':

                if ($this->postId == null) {
                    $response = $this->getAllPosts($this->userId);
                } else {
                    $response = $this->getPost($this->userId, $this->postId);
                }

                break;
//            case 'POST':
//                
//                break;
//            case 'PUT':
//                
//                break;
//            case 'DELETE':
//                
//                break;
            default:

                $response = array(
                    'code' => 404,
                    'section' => $this->logsSection . '-processRequest',
                    'json' => json_encode([
                        'postId' => $this->postId,
                        'userId' => $this->userId
                    ])
                );
                    
                if (USE_LOGS)
                    $this->logs->add(array(
                        'userId' => $this->userId, 
                        'response' => $response)
                    );

                break;
        }

        if ($response) {
            if (USE_LOGS)
                $this->logs->saveLogs();
            if (USE_OB_GZHANDLER) {
                ob_start("ob_gzhandler");
                echo $this->utilities->show_response($response);
                ob_end_flush();
            } else {
                echo $this->utilities->show_response($response);
            }
        }
        
    }
    
    private function getAllPosts($user_id = null) {
        
        try {
            
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                    'GET',
                    'https://jsonplaceholder.typicode.com/posts',
//                    ['connect_timeout' => 5]
            );
            
//            var_dump($res);
            
            if($res->getStatusCode() == 200) {
                
                $contents = $res->getBody()->getContents();
                
                if($this->utilities->is_valid_json($contents)) {
                    $data = (array) json_decode($contents, true);
                
                    $response = array(
                        'code' => 200,
                        'section' => $this->logsSection . '-getAllPosts',
                        'json' => json_encode([
                            'user_id' => $user_id
                        ]),
                        'message' => sprintf($this->txt->t('found %s posts'), count($data))
                    );

                    if(USE_GZCOMPRESS) {

                        $response['body'] = base64_encode(gzcompress(serialize($data)));
                        $response['gzcompress'] = true;

                    } else {

                        $response['body'] = $data;

                    }

                    if ((USE_LOGS) && (SAVE_LOGS_WITH_CODE_200))
                        $this->logs->add(array(
                            'user_id' => $this->userId,
                            'response' => $response
                        ));

                    return $response;
                
                }
                
                // TODO more error info to log
                
            } else {
                
                // TODO more error info to log
                
            }
            
        } catch (Exception $ex) {
            
            $this->errors[] = $ex->getMessage();
            
        }
        
        $response = array(
            'code' => 500,
            'section' => $this->logsSection . '-getAllPosts',
            'json' => json_encode([
                'user_id' => $user_id,
                'errors' => $this->errors
            ]),
        );
        
        if(USE_LOGS)
            $this->logs->add(array(
                'user_id' => $this->userId,
                'response' => $response
            ));
        
        return $response;
        
    }
    
    private function getPost($user_id = null, $post_id = null) {
        
        if($post_id !== null) {
        
            try {

                $client = new \GuzzleHttp\Client();
                
                $res = $client->request(
                        'GET',
                        'https://jsonplaceholder.typicode.com/posts/' . (int) $post_id,
                );

                if($res->getStatusCode() == 200) {

                    $contents = $res->getBody()->getContents();

                    if($this->utilities->is_valid_json($contents)) {
                        
                        $data = (array) json_decode($contents, true);

                        $response = array(
                            'code' => 200,
                            'section' => $this->logsSection . '-getPost',
                            'json' => json_encode([
                                'user_id' => $user_id,
                                'post_id' => $post_id
                            ]),
                            'body' => $data
                        );

                        if ((USE_LOGS) && (SAVE_LOGS_WITH_CODE_200))
                            $this->logs->add(array(
                                'user_id' => $this->userId,
                                'response' => $response
                            ));

                        return $response;
                        
                    }
                    
                    // TODO more error info to log

                } else {
                    
                    // TODO more error info to log
                    
                }

            } catch (Exception $ex) {
                
                $this->errors[] = $ex->getMessage();
                
            }
            
        }
        
        $response = array(
            'code' => 500,
            'section' => $this->logsSection . '-getPost',
            'json' => json_encode([
                'user_id' => $user_id,
                'post_id' => $post_id,
                'errors' => $this->errors
            ]),
        );
        
        if(USE_LOGS)
            $this->logs->add(array(
                'user_id' => $this->userId,
                'response' => $response
            ));
        
        return $response;
        
    }
    
}

?>