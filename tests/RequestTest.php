<?php

include __DIR__ . '/../bootstrap.php';

use Deaduseful\Opensrs\Request;
use Deaduseful\Opensrs\Response;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testModifyContactSet()
    {
        $content = file_get_contents(__DIR__ . '/modify-expected.json');
        $json = json_decode($content, true);
        $action = $json['action'];
        $attributes = $json['attributes'];
        $items = $json['items'];
        $result = Request::encode($action, $attributes, $items);
        $expected = file_get_contents(__DIR__ . '/modify-test.xml');
        $expectedParsed = Response::parseXml($expected);
        $resultParsed = Response::parseXml($result);
        $this->assertEquals($expectedParsed, $resultParsed);
    }
}