<?php

include __DIR__ . '/../bootstrap.php';

use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    public function testModify()
    {
        $service = new Deaduseful\Opensrs\Service();
        $request = file_get_contents(__DIR__ . '/modify-test.xml');
        $content = $service->getContents($request);
        $expected = file_get_contents(__DIR__ . '/modify-response.xml');
        $this->assertEquals($expected, $content);
    }
}