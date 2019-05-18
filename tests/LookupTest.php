<?php

include '../vendor/autoload.php';
include '../config.php';

use Deaduseful\Opensrs\Lookup;
use PHPUnit\Framework\TestCase;

class lookupTest extends TestCase
{
    /**
     */
    public function testLookup()
    {
        $query = 'example.com';
        $lookup = new Lookup();
        $result = $lookup->lookup($query, 'check_transfer');
        $this->assertIsString($result['response']);
        $this->assertIsInt($result['code']);
        $this->assertIsString($result['status']);
    }
}