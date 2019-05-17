<?php

include '../vendor/autoload.php';

use Deaduseful\Opensrs\Lookup;
use PHPUnit\Framework\TestCase;

class lookupTest extends TestCase
{
    /**
     * @expectedException  DomainException
     */
    public function testLookup()
    {
        $query = 'example.com';
        $lookup = new Lookup();
        $lookup->checkTransfer($query);
    }
}