<?php

use Deaduseful\Opensrs\FastLookup;
use PHPUnit\Framework\TestCase;

class FastLookupTest extends TestCase
{
    public function testCheckDomain()
    {
        $query = 'example.com';
        $fastLookup = new FastLookup();
        $fastLookup->checkDomain($query);
        $result = $fastLookup->getResult();
        $this->assertIsString($result['response']);
        $this->assertIsInt($result['code']);
        $this->assertIsString($result['status']);
    }
}