<?php

require_once '../vendor/autoload.php';
require_once '../config.php';

$query = isset($argv[1]) ? $argv[1] : 'example.com';

try {
    $lookup = new Deaduseful\Opensrs\Lookup();
    $result = $lookup->checkTransfer($query);
    if ($result) {
        echo 'Transferable.';
    } else {
        echo 'Not Transferable.';
    }
} catch (Exception $exception) {
    echo $exception->getMessage();
}
echo PHP_EOL;
