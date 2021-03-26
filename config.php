<?php

//error_reporting(E_ALL & ~(E_STRICT|E_NOTICE|E_WARNING));
error_reporting(E_ALL);

set_time_limit(0);

mb_internal_encoding('UTF-8');

//date_default_timezone_set('Europe/Warsaw');
date_default_timezone_set('UTC');

$dbUserName = '34139804_bugiera_a'; //getenv('MYSQL_USER_NAME');
$dbUserPassword = 'ueW2@eirwsfty'; //getenv('MYSQL_USER_PASSWORD');

$config = array(
    'API_NAME' => 'AlleKurier API',
    'USE_LOGS' => true,
    'SAVE_LOGS_WITH_CODE_200' => false,
    'SAVE_LOGS_WITH_CODE_201' => false,
    'SAVE_LOGS_WITH_CODE_200_CRON' => false,
    'SAVE_LOGS_WITH_CODE_404_CRON' => false,
    'DB_HOST' => 'serwer2136086.home.pl', //'database',
    'DB_NAME' => '34139804_bugiera_a', //'_api',
    'DB_USERNAME' => $dbUserName,
    'DB_PASSWORD' => $dbUserPassword,
    'SERVER_HTTP_HOST' => $_SERVER['HTTP_HOST'],
    'TOKEN_KEY' => '', // from ../data/client_secret.json
    'TOKEN_ALGORITHM' => 'HS256',
    'TOKEN_EXPIRATION' => '1800',
    'TOKEN_NOT_BEFORE' => 0,
    'TOKEN_EARLIER' => 600,
    'TOKEN_ISSUER' => 'allekurier_test',
    'DB_STATISTICS_STORES_FROM' => array(
        'host' => 'database',
        'db_name' => '_api',
        'username' => $dbUserName,
        'password' => $dbUserPassword
    ),
    'DB_STATISTICS_EVENTS_FROM' => array(
        'host' => 'database2',
        'db_name' => '_api2',
        'username' => $dbUserName,
        'password' => $dbUserPassword
    ),
    'DB_STATISTICS_FROM' => array(
        'host' => 'database3',
        'db_name' => '_api3',
        'username' => $dbUserName,
        'password' => $dbUserPassword
    ),
    'DB_STATISTICS_SAVE' => array(
        'host' => 'database4',
        'db_name' => '_stat',
        'username' => $dbUserName,
        'password' => $dbUserPassword
    ),
    'DB_LOGS' => array(
        'host' => 'serwer2136086.home.pl', //'database',
        'db_name' => '34139804_bugiera_c', //'_logs',
        'username' => '34139804_bugiera_c', //$dbUserName,
        'password' => $dbUserPassword
    ),
    'OAUTH_FB_APP_ID' => '123',
    'OAUTH_FB_GRAPH_API_VERSION' => 'v6.0',
    'OAUTH_APPLE_CLIENT_ID' => 'com.abc.def.ghi', // my app id
    'OAUTH_APPLE_KID' => 'ABC', // identifier for private key
    'OAUTH_APPLE_ISS' => 'DEF', // team identifier
    'OAUTH_APPLE_JWT_ALG' => 'ES256',
    'OAUTH_APPLE_JWT_EXP' => 3600,
    'APPLE_DEV_APP_SPECIFIC_SHARED_SECRET' => '', // from ../data/client_secret.json
    'APPLE_DEV_VERIFY_RECEIPT' => 'https://buy.itunes.apple.com/verifyReceipt',
    'G_RECAPTCHA_URL' => 'https://www.google.com/recaptcha/api/siteverify',
    'G_RECAPTCHA_REQUIRED' => false,
    'COOKIE_LIFETIME' => 1800,
    'COOKIE_PATH' => '/',
    'COOKIE_DOMAIN' => $_SERVER['HTTP_HOST'],
    'USE_CACHE' => false,
    'PATH_CACHE' => '/cache',
    'PATH_DATA' => '/data',
    'PATH_DATA_STORES_STAT' => '/stores-stat-files',
    'PATH_DATA_MESSAGES' => '/messages-files',
    'INCORRECT_LOGIN_ALLOWED' => 10,
    'ACCOUNT_ACTIVITY' => '365 days',
    'ACCOUNT_TRIAL' => '14 days',
    'LOGIN_CODE_FORMAT' => '/^[a-zA-Z0-9]{32}[-]{3}[a-zA-Z0-9]{32}$/i',
    'AUTO_LOGIN_AFTER_REGISTRATION' => true,
    'SAVE_FULL_STATISTICS' => true,
    'USE_GZCOMPRESS' => true,
    'USE_OB_GZHANDLER' => true,
    'MAXIMUM_NUMBER_OF_ACTIVE_TOKENS' => 10000,
    'ALLOWED_API_VERSIONS' => array('v1'),
    'ALLOWED_LANGUAGES' => array('pl','en'),
    'ALLOWED_STORES_STAT_FILES_TYPES' => array('text/csv','text/plain'),
    'ALLOWED_MESSAGES_FILES_TYPES' => array('image/png','image/jpeg','application/zip','text/plain','application/pdf','application/octet-stream'),
    'FASTSPRING_API_CREDENTIALS_USERNAME' => '', // from ../data/client_secret.json
    'FASTSPRING_API_CREDENTIALS_PASSWORD' => '', // from ../data/client_secret.json
    'MAPS_API_KEY' => '', // from ../data/client_secret.json
    'G_RECAPTCHA_KEY' => '', // from ../data/client_secret.json
    'G_RECAPTCHA_SECRET_KEY' => '', // from ../data/client_secret.json
    'OAUTH_FB_APP_SECRET' => '', // from ../data/client_secret.json
    'OAUTH_APPLE_PRIVATE_KEY' => '../data/AuthKey_ABC.p8',
    'OAUTH_GOOGLE_CLIENT_DEV_REDIRECT_URI' => 'https://' . $_SERVER['HTTP_HOST'] . '/orders-cron',
    'OAUTH_GOOGLE_CLIENT_DEV_KEY_LOCATION' => '../data/google-api/client_secret_abc.json',
    'OAUTH_GOOGLE_CLIENT_DEV_TOKEN_FILE' => '../data/google-api/token.txt',
    'OAUTH_GOOGLE_CLIENT_DEV_SCOPES' => array(
        'email',
        'profile',
        'https://www.googleapis.com/auth/androidpublisher',     // used in: /orders-cron
//        Google_Service_Gmail::MAIL_GOOGLE_COM,
    ),
    'OAUTH_GOOGLE_CLIENT_ID' => '', // from ../data/client_secret_xyz.json
    'OAUTH_GOOGLE_CLIENT_SECRET' => '', // from ../data/client_secret_tuw.json
    'OAUTH_GOOGLE_CLIENT_KEY_LOCATION' => '../data/google-api/client_secret_jkl.json',
    'OAUTH_GOOGLE_CLIENT_SCOPES' => array(
        'email',        // https://www.googleapis.com/auth/userinfo.email
        'profile',      // https://www.googleapis.com/auth/userinfo.profile
    ),
    'FIREBASE_CREDENTIALS_LOCATION' => '../data/abc-firebase-adminsdk-abc-abc.json',
    'CLIENT_SECRET_LOCATION' => '../data/client_secret.json',
);

$oauth_google = @file_get_contents($config['OAUTH_GOOGLE_CLIENT_KEY_LOCATION']);
if((isset($oauth_google)) && (!empty($oauth_google)) && ($oauth_google !== null)){
    $r = json_decode($oauth_google, true);
}
$config['OAUTH_GOOGLE_CLIENT_ID'] = (isset($r['web']['client_id'])) ? $r['web']['client_id'] : null;
$config['OAUTH_GOOGLE_CLIENT_SECRET'] = (isset($r['web']['client_secret'])) ? $r['web']['client_secret'] : null;

$client_secret = @file_get_contents($config['CLIENT_SECRET_LOCATION']);
if((isset($client_secret))&&(!empty($client_secret))&&($client_secret !== null)){
    $r = json_decode($client_secret, true);
    if((isset($r))&&(!empty($r))&&(is_array($r))) {
        $config = array_merge($config,$r);
    }
}

if( strpos( $_SERVER['HTTP_HOST'], '.develop.' ) !== false ) {
    $config['SAVE_LOGS_WITH_CODE_200'] = true;
    $config['SAVE_LOGS_WITH_CODE_201'] = true;
    $config['SAVE_LOGS_WITH_CODE_200_CRON'] = true;
    $config['SAVE_LOGS_WITH_CODE_404_CRON'] = true;
    $config['APPLE_DEV_VERIFY_RECEIPT'] = 'https://sandbox.itunes.apple.com/verifyReceipt';
}

foreach($config as $key => $value) {
    if(defined($key)) {
        // runkit_constant_redefine($key,$value);
    } else {
        DEFINE($key,$value);
    }
}

$config = array(
    'VALIDATE_LOGIN_INPUT_LOGIN' => true,
    'FORCE_SPECIAL_CHARACTERS_IN_PASSWORD_FOR_THE_NEW_USER' => false,
    'FORCE_SPECIAL_CHARACTERS_IN_PASSWORD_WHEN_UPDATING' => false,
    'USE_IPINFO' => false,
    'MOBILE_NUMBER_FORMAT_REQUIRED' => false,
    'MOBILE_NUMBER_FORMAT' => '/^[0-9]{3}[-]{1}[0-9]{3}[-]{1}[0-9]{3}$/i',
    'MOBILE_NUMBER_INPUT_MASK' => '999-999-999',
    'API_URL' => 'https://' . $_SERVER['HTTP_HOST'] . '/'
);

?>