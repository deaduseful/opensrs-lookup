<?php

use Deaduseful\Opensrs\TldChart;

include __DIR__ . '/../src/Opensrs/TldChart.php';

$tldChart = new TldChart();
echo json_encode($tldChart->getTlds());
