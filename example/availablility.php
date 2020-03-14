<?php

require_once '../vendor/autoload.php';
require_once '../config.php';

$query = isset($argv[1]) ? $argv[1] : 'example.com';

try {
    $lookup = new Deaduseful\Opensrs\FastLookup();
    $check = $lookup->available($query);
    if ($check) {
        echo 'Available.';
    } else {
        echo 'Taken.';
    }
} catch (Exception $exception) {
    echo $exception->getMessage();
}
echo PHP_EOL;
