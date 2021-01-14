<?php

include '../vendor/autoload.php';
include '../config.php';

use Deaduseful\Opensrs\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testModifyContactSet()
    {
        $exampleEmail = 'test@example.com';
        $contactSetTypes = ['admin', 'billing', 'tech', 'owner'];
        $contactSet = [];
        foreach ($contactSetTypes as $type) {
            $contactSet[$type] = [];
            $contactSet[$type]['email'] = $exampleEmail;
        }
        $attributes = [];
        $attributes['affect_domains'] = 1;
        $attributes['contact_set'] = $contactSet;
        $attributes['report_email'] = $exampleEmail;
        $attributes['data'] = 'contact_info';
        $action = 'MODIFY';
        $items = ['domain' => 'example.com'];
        $result = Request::encode($action, $attributes, $items);
        $expected = file_get_contents('request-modify.html');
        $this->assertEquals($expected, $result);
    }
}