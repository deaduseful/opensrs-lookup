<?php

include __DIR__ . '/../bootstrap.php';

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

    public function testGetDataByTld()
    {
        $key = '.com';
        $domainPricing = new DomainPricing();
        $result = $domainPricing->getDataByTld($key);
        $expected = json_decode(file_get_contents(__DIR__ . '/data/testGetDataByTld.json'), true);
        $this->assertEquals($expected, $result);
    }
}