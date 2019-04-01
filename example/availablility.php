<?php

include '../src/Opensrs/FastLookup.php';

/**
 * @param $query
 * @return bool
 * @throws Exception
 */
function checkAvailability($query)
{
    $lookup = new Deaduseful\Opensrs\FastLookup($query);
    $result = $lookup->getResult();
    if ($result['status'] === 'taken') {
        return false;
    }
    if ($result['status'] === 'available') {
        return true;
    }
    throw new Exception('No result.');
}

$query = isset($argv[1]) ? $argv[1] : 'example.com';

try {
    $check = checkAvailability($query);
    if ($check) {
        echo 'Available.';
    } else {
        echo 'Taken.';
    }
} catch (Exception $exception) {
    echo $exception->getMessage();
}
echo PHP_EOL;
