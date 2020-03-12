<?php

require_once '../vendor/autoload.php';
require_once '../config.php';

$searchString = isset($argv[1]) ? $argv[1] : 'example';

$tlds = ['.com', '.net', '.org'];
$services = ['lookup', 'suggestion', 'premium', 'personal_names'];

$lookup = new Deaduseful\Opensrs\Lookup();

$result = $lookup->suggest($searchString, $tlds, $services);
$content = $lookup->getContent();
file_put_contents('../tests/suggest.xml', $content);
$result = $lookup->formatResult($content);
var_dump($result);
