<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Http\Request;

$request = Request::capture();

//'/v2/{clientId}/{mkt}/{provider}/{configId}/{timestamp}';
$route = $request->segment(1);
    // Check if SSL
    if ((isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) || (isset($_SERVER['HTTPS']) &&    $_SERVER['SERVER_PORT'] == 443)) {
        $_SERVER['HTTPS'] = true;
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
        $_SERVER['HTTPS'] = true;
    } else {
        $_SERVER['HTTPS'] = false;
    }

$current_url = ($_SERVER['HTTPS'] ? "https" : "http" ) . "://". $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];

if (file_exists(__DIR__ . '/../we.v2/' . $route . '.php')) {
    require __DIR__ . '/../we.v2/bootstrap.php';


    //AUTH CHECK!



    //SERVICE CALL
    $response = include __DIR__ . '/../we.v2/' . $route . '.php';


    if(is_null($response)) {
        return;
    }

    if (isset($request->callback)) {
        header('Content-Type: application/javascript');
    } elseif (isset($response['redirect'])) {
        // 302 Found
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        header("Location: " . $response['redirect'], TRUE, 302);
        exit();
    } elseif (isset($response['Content-Type'])) {
        header('Content-Type: ' . $response['Content-Type']);
    } else {
        header('Content-Type: application/json');
    }
    if (isset($response['content'])) {
        print_r($response['content']);
    } else {
        print_r(json_encode(
            $response
        ));
    }
    exit;
} else {
    // We'll be outputting a JSON
    header('Content-Type: application/json');
    print_r(json_encode(
        [
            'status' => false,
            'message' => 'UNKNOWN METHOD'
        ]
    ));
    exit;
}
