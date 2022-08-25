<?php

use Deaduseful\Opensrs\DomainPricing;

require_once '../vendor/autoload.php';
require_once '../config.php';

$domainPricing = new DomainPricing();
echo json_encode($domainPricing->getData());
