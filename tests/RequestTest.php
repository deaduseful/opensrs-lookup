<?php

include __DIR__ . '/../bootstrap.php';

use Deaduseful\Opensrs\RequestBuilder;
use Deaduseful\Opensrs\ResponseParser;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testModifyContactSet()
    {
        $content = file_get_contents(__DIR__ . '/data/modify-expected.json');
        $json = json_decode($content, true);
        $action = $json['action'];
        $attributes = $json['attributes'];
        $items = $json['items'];
        $request = new RequestBuilder();
        $result = $request->encode($action, $attributes, $items);
        $expected = file_get_contents(__DIR__ . '/data/modify-test.xml');
        $response = new ResponseParser();
        $expectedParsed = $response->parseXml($expected);
        $resultParsed = $response->parseXml($result);
        $this->assertEquals($expectedParsed, $resultParsed);
    }
}