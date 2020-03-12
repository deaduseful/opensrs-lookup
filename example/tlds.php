<?php

use Deaduseful\Opensrs\TldChart;

require_once '../vendor/autoload.php';
require_once '../config.php';

$tldChart = new TldChart();
echo json_encode($tldChart->getTlds());
