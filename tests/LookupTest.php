<?php

include __DIR__ . '/../bootstrap.php';

use Deaduseful\Opensrs\Lookup;
use Deaduseful\Opensrs\ResponseParser;
use PHPUnit\Framework\TestCase;

class LookupTest extends TestCase
{

    public function testLookup()
    {
        $query = 'example.com';
        $lookup = new Lookup();
        $result = $lookup->lookup($query);
        $this->assertIsString($result['response']);
        $this->assertIsInt($result['code']);
        $this->assertIsString($result['status']);
    }

    public function testAvailable()
    {
        $query = 'example.com';
        $lookup = new Lookup();
        $result = $lookup->available($query);
        $this->assertFalse($result);
    }

    public function testCheckTransfer()
    {
        $query = 'example.com';
        $lookup = new Lookup();
        $result = $lookup->checkTransfer($query);
        $this->assertFalse($result);
    }

    public function testSuggest()
    {
        $searchString = 'example';
        $tlds = ['.com', '.net', '.org'];
        $lookup = new Lookup();
        $result = $lookup->suggest($searchString, $tlds);
        $this->assertIsArray($result);
    }

    public function testGetDomain()
    {
        $query = 'phurix.com';
        $lookup = new Lookup();
        $result = $lookup->getDomain($query);
        $expected = json_decode(file_get_contents(__DIR__ . '/data/testGetDomain.json'), true);
        $this->assertEquals($expected, $result);
    }

    public function testGetDomainsByExpireDate()
    {
        $lookup = new Lookup();
        $result = $lookup->getDomainsByExpireDate();
        $this->assertIsString($result['response']);
        $this->assertIsInt($result['code']);
        $this->assertIsString($result['status']);
    }

    public function testGetDomainStatus()
    {
        $query = 'phurix.com';
        $lookup = new Lookup();
        $result = $lookup->getDomain($query, 'status');
        $expected = json_decode(file_get_contents(__DIR__ . '/data/testGetDomainStatus.json'), true);
        $this->assertEquals($expected, $result);
    }
}