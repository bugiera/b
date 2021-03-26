<?php

require_once './config.php';

//if( strpos( $_SERVER['HTTP_HOST'], '.develop.' ) !== false ) {} else {
//    Sentry\init(['dsn' => '' ]);
//}

require './vendor/autoload.php';

require './objects/Database.php';
require './objects/Utilities.php';
//require './objects/Cache.php';

require './objects/LogsGateway.php';
require './objects/LogsController.php';

require './objects/TextsGateway.php';
require './objects/TextsController.php';

require './objects/PostsGateway.php';
require './objects/PostsController.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Referrer-Policy: origin");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE,HEAD");
header("Access-Control-Allow-Headers: Access-Control-Allow-Credentials, Referer, Origin, Access-Control-Max-Age, Referrer-Policy, Content-Type, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Authorization, X-Requested-With, Access-Control-Allow-Origin");

$date = new DateTime();

$database = new Database(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);
$db = $database->getConnection();
if($database->hasError()) {
    
    header('HTTP/1.1 500 Error Establishing a Database Connection');
    
    $array = array(
        'code' => 500,
        'status' => 'error',
        'message' => 'connection error',
        'errors' => array('Database connection failure.'),
        'date' => $date,
        'timestamp' => $date->getTimestamp(),
        'gzcompress' => false,
        'data' => null,
    );
    
    exit(json_encode($array,JSON_PRETTY_PRINT));
    
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/api', '', $uri);

$pattern = "/(v|V)\\d/i";
preg_match($pattern, $uri, $matches, PREG_OFFSET_CAPTURE);
if (!$matches) {
    $uri = '/v1' . $uri;
}

$uriParts = explode('/', $uri);
$apiVersion = $uriParts[1];

$lg = null;
$keysUriParts = array_keys($uriParts);
$endKeyUriParts = end($keysUriParts);
if(in_array($uriParts[$endKeyUriParts], ALLOWED_LANGUAGES)) {
    $lg = $uriParts[$endKeyUriParts];
    unset($uriParts[$endKeyUriParts]);
}
unset($uriParts[1]);

$uriParts = array_values(array_filter($uriParts));
array_unshift($uriParts,$apiVersion);

$uri = str_replace('/'.$apiVersion, '', $uri);
if($lg !== null) {
    $uri = str_replace('/'.$lg, '', $uri);
}

switch (strtolower($lg)) {
    case 'pl':
    case 'pl_pl':
        $lg = 'pl_pl';
        break;
    case 'en':
    case 'en_en':
    default:
        $lg = 'en_en';
        break;
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

$routes = [
// posts
    'posts.get.all' => [
        'method' => 'GET',
        'expression' => '/^\/posts\/?$/',
        'controller_name' => 'PostsController',
        'controller_method' => 'processRequest',
        'authenticate' => false, // TODO in future: true,
        'language' => 'en_en'
    ],
    'posts.get.one' => [
        'method' => 'GET',
        'expression' => '/^\/posts\/(\d+)\/?$/',
        'controller_name' => 'PostsController',
        'controller_method' => 'processRequest',
        'authenticate' => false, // TODO in future: true,
        'language' => 'en_en'
    ],
    'logs.get.all' => [
        'method' => 'GET',
        'expression' => '/^\/logs\/?$/',
        'controller_name' => 'LogsController',
        'controller_method' => 'processRequest',
        'authenticate' => false, // TODO in future: true,
        'language' => 'en_en'
    ],
// users
//    'confirm.code.get' => [
//        'method' => 'GET',
//        'expression' => '/^\/confirm\/(verify-account|verify-email|reset-password)\/code\/([a-zA-z0-9]+)\/email\/(.+)\/?$/',
//        'controller_name' => 'UsersController',
//        'controller_method' => 'processRequestConfirm',
//        'authenticate' => false,
//        'language' => 'en_en'
//    ],
//    'confirm.put' => [
//        'method' => 'PUT',
//        'expression' => '/^\/confirm\/(mobile)\/?$/',
//        'controller_name' => 'UsersController',
//        'controller_method' => 'processRequestConfirm',
//        'authenticate' => true,
//        'language' => 'en_en'
//    ],
//    'users.stream.put' => [
//        'method' => 'PUT',
//        'expression' => '/^\/stream\/?$/',
//        'controller_name' => 'UsersController',
//        'controller_method' => 'processRequestStream',
//        'authenticate' => true,
//        'language' => 'en_en'
//    ],
];

$utilities = new Utilities();

//logs stuff
$dbLogs = @DB_LOGS;
if(
        ($dbLogs) &&
        (is_array($dbLogs)) &&
        (!empty($dbLogs))
) {
                
    $database_logs = new Database($dbLogs['host'], $dbLogs['db_name'], $dbLogs['username'], $dbLogs['password']);
    
    if($database_logs->hasError()) {
        $dbLogs = $db;
    } else {
        $dbLogs = $database_logs->getConnection();
    }
                
} else {
    $dbLogs = $db;
}

$logs = new LogsController($dbLogs, false, false, $requestMethod, $uriParts, false);
$text = new TextsController($db, $logs, $requestMethod, $uriParts);
$logs = new LogsController($dbLogs, false, $text, $requestMethod, $uriParts, false);

if (!in_array($apiVersion, ALLOWED_API_VERSIONS)) {

    $response['code'] = 403;
    $response['message'] = $text->t('This API version is unsupported.');
    $response['section'] = 'index-api-version';
    $response['json'] = json_encode([
        'uri' => $uri,
        'requestMethod' => $requestMethod,
        'apiVersion' => $apiVersion
    ]);

    if (USE_LOGS) {
        $logs->add(array(
            'user_id' => null,
            'response' => $response
        ));
        $logs->saveLogs();
    }

    exit($utilities->show_response($response));
    
}

$routeFound = null;
$language = null;
$authenticate = true;

foreach ($routes as $route) {
    if ($route['method'] == $requestMethod && preg_match($route['expression'],$uri)) {
        $routeFound = $route;
        if ($route['authenticate'] == false) {
            $authenticate = false;
        }
        $language = $route['language'];
        break;
    }
}
if (!$routeFound) {
    
    $response['code'] = 404;
    $response['section'] = 'index-route';
    $response['message'] = $text->t('Route Not Found.');
    $response['json'] = json_encode([
        'uri' => $uri,
        'requestMethod' => $requestMethod
    ]);
    if(USE_LOGS){
        $logs->add(array(
            'user_id' => null,
            'response' => $response)
        );
        $logs->saveLogs();
    }
    exit($utilities->show_response($response));
    
}

$fromJwt = $utilities->authenticate();
$logs = new LogsController($dbLogs, false, $text, $requestMethod, $uriParts, $fromJwt);

if (($authenticate)&&(!$fromJwt)) {
    
    $response['code'] = 401;
    $response['section'] = 'index-authenticate';
    $response['message'] = $text->t('Unauthorized');
    $response['json'] = json_encode([
        'uri' => $uri,
        'requestMethod' => $requestMethod
    ]);
    if(USE_LOGS){
        $logs->add(array(
            'user_id' => null,
            'response' => $response
        ));
        $logs->saveLogs();
    }
    exit($utilities->show_response($response));
    
}

$text = new TextsController($db, $logs, $requestMethod, $uriParts, $fromJwt);
$language = ($lg != null) ? $lg : $language;
$text->setLanguage($language);



/*
 * 
 * extra variables from the database and personalized for the user
 * 
 * 
$variables = array();

$all_variables = (isset($fromJwt->data->user_id)) ? $usersGateway->getVariables($fromJwt->data->user_id) : $usersGateway->getVariables();
foreach($all_variables as $key => $value) {
    if(is_array($value)) {
        $variables[$key] = $value[$language];
    } else {
        $variables[$key] = $value;
    }
}

$variables = array_merge($config,$variables);

foreach($variables as $key => $value) {
    
    $new_value = $utilities->parse($value, $variables, false);
    $variables[$key] = $new_value;
    
}

$text->setVariables($variables);

foreach($variables as $key => $value) {
    if(defined($key)) {
        // runkit_constant_redefine($key,$value);
    } else {
        DEFINE($key,$value);
    }
}
*/



$utilities = new Utilities();

unset($usersGateway);

$controller_name = $route['controller_name'];
$controller_method = $route['controller_method'];

if($controller_name == 'LogsController') {
    $controller = new $controller_name($dbLogs, $logs, $text, $requestMethod, $uriParts, $fromJwt);
} else {
    $controller = new $controller_name($db, $logs, $text, $requestMethod, $uriParts, $fromJwt);
}
$controller->$controller_method();

?>