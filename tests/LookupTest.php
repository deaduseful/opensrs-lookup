<?php

include __DIR__ . '/../bootstrap.php';
include __DIR__ . '/MockRequestClient.php';

use Deaduseful\Opensrs\Lookup;
use Deaduseful\Opensrs\MockRequestClient;
use PHPUnit\Framework\TestCase;

class LookupTest extends TestCase
{
    private MockRequestClient $mockRequestClient;

    protected function setUp(): void
    {
        $this->mockRequestClient = new MockRequestClient();
        
        // Load test data files
        $this->mockRequestClient
            ->setMockResponse('lookup', file_get_contents(__DIR__ . '/data/testLookup.xml'))
            ->setMockResponse('check_transfer', file_get_contents(__DIR__ . '/data/testCheckTransfer.xml'))
            ->setMockResponse('suggest', file_get_contents(__DIR__ . '/data/testSuggest.xml'))
            ->setMockResponse('get_domain', file_get_contents(__DIR__ . '/data/testGetDomain.xml'))
            ->setMockResponse('get_domain_status', file_get_contents(__DIR__ . '/data/testGetDomainStatus.xml'))
            ->setMockResponse('get_domains_by_expiredate', file_get_contents(__DIR__ . '/data/testGetDomainsByExpireDate.xml'))
            ->setMockResponse('default', file_get_contents(__DIR__ . '/data/testAvailable.xml'));
    }

    public function testLookup()
    {
        $query = 'example.com';
        $lookup = new Lookup('test_user', 'test_key', false, null, $this->mockRequestClient);
        $result = $lookup->lookup($query);
        $this->assertIsString($result['response']);
        $this->assertIsInt($result['code']);
        $this->assertIsString($result['status']);
    }

    public function testAvailable()
    {
        $query = 'example.com';
        $lookup = new Lookup('test_user', 'test_key', false, null, $this->mockRequestClient);
        $result = $lookup->available($query);
        $this->assertFalse($result);
    }

    public function testCheckTransfer()
    {
        $query = 'example.com';
        $lookup = new Lookup('test_user', 'test_key', false, null, $this->mockRequestClient);
        $result = $lookup->checkTransfer($query);
        $this->assertFalse($result);
    }

    public function testSuggest()
    {
        $searchString = 'example';
        $tlds = ['.com', '.net', '.org'];
        $lookup = new Lookup('test_user', 'test_key', false, null, $this->mockRequestClient);
        $result = $lookup->suggest($searchString, $tlds);
        $this->assertIsArray($result);
    }

    public function testGetDomain()
    {
        $query = 'phurix.com';
        $lookup = new Lookup('test_user', 'test_key', false, null, $this->mockRequestClient);
        $result = $lookup->getDomain($query);
        $expected = json_decode(file_get_contents(__DIR__ . '/data/testGetDomain.json'), true);
        $this->assertEquals($expected, $result);
    }

    public function testGetDomainsByExpireDate()
    {
        $lookup = new Lookup('test_user', 'test_key', false, null, $this->mockRequestClient);
        $result = $lookup->getDomainsByExpireDate();
        $this->assertIsString($result['response']);
        $this->assertIsInt($result['code']);
        $this->assertIsString($result['status']);
    }

    public function testGetDomainStatus()
    {
        $query = 'phurix.com';
        $lookup = new Lookup('test_user', 'test_key', false, null, $this->mockRequestClient);
        $result = $lookup->getDomain($query, 'status');
        $expected = json_decode(file_get_contents(__DIR__ . '/data/testGetDomainStatus.json'), true);
        $this->assertEquals($expected, $result);
    }
}