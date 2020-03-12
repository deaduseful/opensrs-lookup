<?php

include '../vendor/autoload.php';
include '../config.php';

use Deaduseful\Opensrs\Lookup;
use PHPUnit\Framework\TestCase;

class lookupTest extends TestCase
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

    public function testAvailableFormatResult()
    {
        $lookup = new Lookup();
        $content = file_get_contents('available.xml');
        $result = $lookup->formatResult($content);
        $actual = json_encode($result);
        $expected = '{"response":"Domain taken","code":211,"status":"unknown","attributes":{"status":"taken"}}';
        $this->assertEquals($expected, $actual);
    }

    public function testTransferabilityFormatResult()
    {
        $lookup = new Lookup();
        $content = file_get_contents('transferability.xml');
        $result = $lookup->formatResult($content);
        $actual = json_encode($result);
        $expected = '{"response":"Query successful","code":200,"status":"success","attributes":{"transferrable":"0","noservice":"0","reason":"Domain status clientTransferProhibited does not allow for transfer"}}';
        $this->assertEquals($expected, $actual);
    }

    public function testSuggestFormatResult()
    {
        $lookup = new Lookup();
        $content = file_get_contents('suggest.xml');
        $result = $lookup->formatResult($content);
        var_dump($result); die();
        $this->assertNotNull($result);
    }
}