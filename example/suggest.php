<?php

require_once '../vendor/autoload.php';
require_once '../config.php';

$searchString = isset($argv[1]) ? $argv[1] : 'example';

$tlds = ['.com', '.net', '.org'];
$services = ['lookup', 'suggestion', 'premium', 'personal_names'];

$action = 'NAME_SUGGEST';
$lookup = new Deaduseful\Opensrs\Lookup();
$attributes = [
    'searchstring' => $searchString,
    'tlds' => $tlds,
    'services' => $services
];

$result = $lookup->execute($attributes, $action);
$content = $lookup->getContent();
file_put_contents('../tests/suggest.xml', $content);
$result = $lookup->formatResult($content);
var_dump($result);
