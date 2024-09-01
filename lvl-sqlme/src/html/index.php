<?php

$super_token = $_GET['token'] ?? (show_source(__FILE__) && die());

$db = new SQLite3('/tmp/db.sqlite');

$stmt = $db->prepare('SELECT token FROM api_tokens WHERE token LIKE :cond AND is_super_token=1');
$stmt->bindValue(':cond', "%$super_token%", SQLITE3_TEXT);

$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (empty($result))
    die("Error: token not found.");

if ($result['token'] !== $super_token)
    die("Error: invalid token.");

echo "Hello Senpai ! 🚩 " . file_get_contents('../flag.txt');
