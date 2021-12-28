<?php

use Deaduseful\Opensrs\Lookup;

require_once '../vendor/autoload.php';
require_once '../config.php';

$query = $argv[1] ?? 'example.com';

try {
    $lookup = new Lookup();
    $result = $lookup->getDomain($query, 'status');
    var_dump($result);
} catch (Exception $exception) {
    echo $exception->getMessage();
}
echo PHP_EOL;
