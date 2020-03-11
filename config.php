<?php

$file = __DIR__ . '/.env';
if (file_exists($file)) {
    $env = file($file, FILE_IGNORE_NEW_LINES);
    foreach ($env as $line) {
        putenv($line);
    }
}
