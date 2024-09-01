<?php
 
$router = new Moxxie\MoxRouter();
 
$router->get('/', function(){
  include_once __DIR__ . '/pages/index.html';
});

$router->post('/module/sha1img', function() {
    $start_timestamp = microtime(true);

    if (empty($_POST['url'])) {
        http_response_code(400);
        die('ERROR : Missing parameter "url"');
    }

    try {
        $sha1img = new Sha1img($_POST['url']);
        $sig = $sha1img->getSignature();
    } catch (Exception $e) {
        http_response_code(500);
        die("ERROR : {$e->getMessage()}");
    }
    
    $end_timestamp = microtime(true);
    header("X-REQUEST-TIMESTAMP: " . $start_timestamp);
    header("X-RESPONSE-TIMESTAMP: " . $end_timestamp);

    $duration = round($end_timestamp - $start_timestamp, 3);
    echo "$sig (generated in $duration seconds).";
});

$router->get('/403', function() {
    http_response_code(403);
    include_once __DIR__ . '/pages/403.html';
});

$router->get('/404', function() {
    http_response_code(404);
    include_once __DIR__ . '/pages/404.html';
});

$router->notFound(function(){

    $is_dir = realpath(PUBLIC_FOLDER . "/{$_SERVER['REQUEST_URI']}");

    if ($is_dir !== false and substr($is_dir, 0, strlen(PUBLIC_FOLDER)) !== PUBLIC_FOLDER) {
        http_response_code(403);
        include_once __DIR__ . '/pages/403.html';
        die('');
    }

    http_response_code(404);
    include_once __DIR__ . '/pages/404.html';
});

$router->run();
