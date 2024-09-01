<?php

ini_set('display_errors','Off');

function get_request($url) {
    $r = file_get_contents($url, false, stream_context_create(['http' => ['ignore_errors' => true, 'follow_location' => false]]));

    if (isset($_GET['http_headers'])) {
        $r = join("\n", $http_response_header) . "\n\n$r";
    }

    return "<pre>\n".htmlentities($r)."</pre>\n";
}

function waf($url) {
    $components = parse_url($url);
    if ($components['scheme'] !== 'http' && $components['scheme'] !== 'https')
        die('Ony http/https protocol are allowed.');
    
    parse_str($components['query'], $_get);
    foreach ($_get as $key => $value)
    {   
        foreach ([' ', '"', "'", '#', '--', '..', ';', '$', '<', '>'] as $illegal) {
            if (str_contains($value, $illegal))
                die('Attack detected.');
        }
    }
}

if (empty($_GET['url']) && empty($_GET['page']))
    (show_source(__FILE__) && die(''));

if (isset($_GET['page']))
    exit(get_request("https://barbhack.fr/2024/fr/".ltrim($_GET['page'], '/')));

if (empty($_GET['token']) || $_GET['token'] !== 'DEEZNUTS')
    die('Not authorized.');

waf($_GET['url']);
echo get_request($_GET['url']);
