<?php

include '../vendor/autoload.php';
include '../config.php';

use Deaduseful\Opensrs\DomainPricing;
use PHPUnit\Framework\TestCase;

class DomainPricingTest extends TestCase
{

    public function testToArray()
    {
        $domainPricing = new DomainPricing();
        $result = $domainPricing->toArray();
        $this->assertIsArray($result);
    }
}