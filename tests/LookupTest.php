<?php

include '../vendor/autoload.php';
include '../config.php';

use Deaduseful\Opensrs\Lookup;
use Deaduseful\Opensrs\Response;
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
        $query = 'example.com';
        $lookup = new Lookup();
        $result = $lookup->getDomain($query);
        $this->assertIsString($result['response']);
        $this->assertIsInt($result['code']);
        $this->assertIsString($result['status']);
    }
}