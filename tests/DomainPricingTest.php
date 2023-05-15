<?php

include __DIR__ . '/../bootstrap.php';

use Deaduseful\Opensrs\DomainPricing;
use PHPUnit\Framework\TestCase;

class DomainPricingTest extends TestCase
{

    public function testToArray()
    {
        $domainPricing = new DomainPricing();
        $result = $domainPricing->toArray();
        $this->assertIsArray($result);
    }

    public function testGetDataByTld()
    {
        $key = '.com';
        $domainPricing = new DomainPricing();
        $result = $domainPricing->getDataByTld($key);
        $expected = [
            'tld' => '.com',
            'type' => 'Generic',
            'website_order' => 1,
            'basic_registration' => 13,
            'basic_transfer' => 13,
            'basic_renewal' => 13,
            'basic_trade' => null,
            'basic_redemption' => 80,
            'startup_registration' => 12,
            'startup_transfer' => 12,
            'startup_renewal' => 12,
            'startup_trade' => null,
            'startup_redemption' => 80,
            'growth_registration' => 11,
            'growth_transfer' => 11,
            'growth_renewal' => 11,
            'growth_trade' => null,
            'growth_redemption' => 80,
            'enterprise_registration' => 10,
            'enterprise_transfer' => 10,
            'enterprise_renewal' => 10,
            'enterprise_trade' => null,
            'enterprise_redemption' => 80,
            'promo_price' => null,
            'promo_start_date' => null,
            'promo_end_date' => null,
            'promo_signup_required' => null,
            'promo_notes' => null,
            'promo_order' => null,
            'notes' => null,
            'tags' => 'Featured',
            'start_date_limit' => null,
            'end_date_limit' => '2023-09-01 0:00',
        ];
        $this->assertEquals($expected, $result);
    }
}