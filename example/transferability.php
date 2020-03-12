<?php

require_once '../vendor/autoload.php';
require_once '../config.php';

/**
 * @param $query
 * @return bool
 * @throws Exception
 */
function checkTransferable($query)
{
    $lookup = new Deaduseful\Opensrs\Lookup();
    $result = $lookup->checkTransfer($query);
    $content = $lookup->getContent();
    file_put_contents('../tests/transferability.xml', $content);
    return $result;
}

$query = isset($argv[1]) ? $argv[1] : 'example.com';

try {
    $check = checkTransferable($query);
    if ($check) {
        echo 'Transferable.';
    } else {
        echo 'Not Transferable.';
    }
} catch (Exception $exception) {
    echo $exception->getMessage();
}
echo PHP_EOL;
