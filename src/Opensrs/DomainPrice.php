<?php

namespace Deaduseful\Opensrs;

/**
 * Domain price.
 */
class DomainPrice
{
    public string $tld;
    public string $type;
    public string $website_order;
    public float $basic_registration;
    public float $basic_transfer;
    public float $basic_renewal;
    public ?float $basic_trade;
    public float $basic_redemption;
    public float $startup_registration;
    public float $startup_transfer;
    public float $startup_renewal;
    public ?float $startup_trade;
    public float $startup_redemption;
    public float $growth_registration;
    public float $growth_transfer;
    public float $growth_renewal;
    public ?float $growth_trade;
    public float $growth_redemption;
    public float $enterprise_registration;
    public float $enterprise_transfer;
    public float $enterprise_renewal;
    public ?float $enterprise_trade;
    public float $enterprise_redemption;
    public ?float $promo_price;
    public ?string $promo_start_date;
    public ?string $promo_end_date;
    public ?string $promo_signup_required;
    public ?string $promo_notes;
    public ?string $promo_order;
    public ?string $notes;
    public string $tags;
    public ?string $start_date_limit;
    public ?string $end_date_limit;

    public function __construct(array $data)
    {
        $this->tld = $data['tld'];
        $this->type = $data['type'];
        $this->website_order = $data['website_order'];
        $this->basic_registration = (float)$data['basic_registration'];
        $this->basic_transfer = (float)$data['basic_transfer'];
        $this->basic_renewal = (float)$data['basic_renewal'];
        $this->basic_trade = $data['basic_trade'] !== '' ? (float)$data['basic_trade'] : null;
        $this->basic_redemption = (float)$data['basic_redemption'];
        $this->startup_registration = (float)$data['startup_registration'];
        $this->startup_transfer = (float)$data['startup_transfer'];
        $this->startup_renewal = (float)$data['startup_renewal'];
        $this->startup_trade = $data['startup_trade'] !== '' ? (float)$data['startup_trade'] : null;
        $this->startup_redemption = (float)$data['startup_redemption'];
        $this->growth_registration = (float)$data['growth_registration'];
        $this->growth_transfer = (float)$data['growth_transfer'];
        $this->growth_renewal = (float)$data['growth_renewal'];
        $this->growth_trade = $data['growth_trade'] !== '' ? (float)$data['growth_trade'] : null;
        $this->growth_redemption = (float)$data['growth_redemption'];
        $this->enterprise_registration = (float)$data['enterprise_registration'];
        $this->enterprise_transfer = (float)$data['enterprise_transfer'];
        $this->enterprise_renewal = (float)$data['enterprise_renewal'];
        $this->enterprise_trade = $data['enterprise_trade'] !== '' ? (float)$data['enterprise_trade'] : null;
        $this->enterprise_redemption = (float)$data['enterprise_redemption'];
        $this->promo_price = $data['promo_price'] !== '' ? (float)$data['promo_price'] : null;
        $this->promo_start_date = $data['promo_start_date'] !== '' ? $data['promo_start_date'] : null;
        $this->promo_end_date = $data['promo_end_date'] !== '' ? $data['promo_end_date'] : null;
        $this->promo_signup_required = $data['promo_signup_required'] !== '' ? $data['promo_signup_required'] : null;
        $this->promo_notes = $data['promo_notes'] !== '' ? $data['promo_notes'] : null;
        $this->promo_order = $data['promo_order'] !== '' ? $data['promo_order'] : null;
        $this->notes = $data['notes'] !== '' ? $data['notes'] : null;
        $this->tags = $data['tags'];
        $this->start_date_limit = $data['start_date_limit'] !== '' ? $data['start_date_limit'] : null;
        $this->end_date_limit = $data['end_date_limit'] !== '' ? $data['end_date_limit'] : null;
    }

    public function toArray(): array
    {
        return [
            'tld' => $this->tld,
            'type' => $this->type,
            'website_order' => $this->website_order,
            'basic_registration' => $this->basic_registration,
            'basic_transfer' => $this->basic_transfer,
            'basic_renewal' => $this->basic_renewal,
            'basic_trade' => $this->basic_trade,
            'basic_redemption' => $this->basic_redemption,
            'startup_registration' => $this->startup_registration,
            'startup_transfer' => $this->startup_transfer,
            'startup_renewal' => $this->startup_renewal,
            'startup_trade' => $this->startup_trade,
            'startup_redemption' => $this->startup_redemption,
            'growth_registration' => $this->growth_registration,
            'growth_transfer' => $this->growth_transfer,
            'growth_renewal' => $this->growth_renewal,
            'growth_trade' => $this->growth_trade,
            'growth_redemption' => $this->growth_redemption,
            'enterprise_registration' => $this->enterprise_registration,
            'enterprise_transfer' => $this->enterprise_transfer,
            'enterprise_renewal' => $this->enterprise_renewal,
            'enterprise_trade' => $this->enterprise_trade,
            'enterprise_redemption' => $this->enterprise_redemption,
            'promo_price' => $this->promo_price,
            'promo_start_date' => $this->promo_start_date,
            'promo_end_date' => $this->promo_end_date,
            'promo_signup_required' => $this->promo_signup_required,
            'promo_notes' => $this->promo_notes,
            'promo_order' => $this->promo_order,
            'notes' => $this->notes,
            'tags' => $this->tags,
            'start_date_limit' => $this->start_date_limit,
            'end_date_limit' => $this->end_date_limit,
        ];
    }

    public function getTld()
    {
        return $this->tld;
    }
}
