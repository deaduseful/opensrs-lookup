<?php

include '../src/Opensrs/Lookup.php';

/**
 * @param $query
 * @return bool
 * @throws Exception
 */
function checkTransferable($query)
{
    $lookup = new Deaduseful\Opensrs\Lookup($query, 'check_transfer');
    $result = $lookup->getResult();
    return $result['transferrable'] === 1;
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
