<?php

$db = new SQLite3('/tmp/db.sqlite');

$db->exec(file_get_contents(__DIR__ . "/db.sql"));