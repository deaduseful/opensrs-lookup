<?php

require_once '../vendor/autoload.php';
require_once '../config.php';

$searchString = isset($argv[1]) ? $argv[1] : 'example';

$tlds = ['.com', '.net', '.org'];
$services = ['lookup', 'suggestion', 'premium', 'personal_names'];

$lookup = new Deaduseful\Opensrs\Lookup();

$result = $lookup->suggest($searchString, $tlds, $services);
var_dump($result['attributes']);
