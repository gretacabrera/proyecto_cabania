<?php

function loadEnv($file) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        list($key, $value) = explode('=', trim($line), 2);
        putenv("$key=$value");
    }
}

loadEnv(__DIR__ . '/.env');

?>