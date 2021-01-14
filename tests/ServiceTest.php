<?php

include '../vendor/autoload.php';
include '../config.php';

use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    public function testModify()
    {
        $service = new Deaduseful\Opensrs\Service();
        $request = file_get_contents('modify-test.xml');
        $content = $service->getContents($request);
        $expected = file_get_contents('modify-response.xml');
        $this->assertEquals($expected, $content);
    }
}