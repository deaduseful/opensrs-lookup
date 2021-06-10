<?php

namespace Deaduseful\Opensrs;

class Lookup extends Service
{

    const ACTION_CHECK_TRANSFER = 'check_transfer';
    const ACTION_LOOKUP = 'lookup';
    const ACTION_NAME_SUGGEST = 'name_suggest';
    const SERVICES_SUGGEST = ['lookup', 'suggestion', 'premium', 'personal_names'];
    const STATUS_AVAILABLE = 'available';
    const STATUS_TAKEN = 'taken';
    const STATUS_TRANSFER = 'transferrable';
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @param string $query
     * @return bool|null
     */
    public function checkTransfer(string $query)
    {
        $attributes = ['domain' => $query];
        $result = $this->perform(self::ACTION_CHECK_TRANSFER, $attributes);
        $attributes = $result['attributes'];
        $key = self::STATUS_TRANSFER;
        if (array_key_exists($key, $attributes)) {
            $transferable = $attributes[$key];
            if ($transferable === '1') {
                return true;
            }
            if ($transferable === '0') {
                return false;
            }
        }
        return null;
    }

    /**
     * Perform lookup.
     * @param string $query
     * @param string $action
     * @return array
     */
    public function lookup(string $query, string $action = self::ACTION_LOOKUP)
    {
        $attributes = ['domain' => $query];
        return $this->perform($action, $attributes);
    }

    /**
     * @param string $query
     * @return bool|null
     */
    public function available(string $query)
    {
        $attributes = ['domain' => $query];
        $action = self::ACTION_LOOKUP;
        $result = $this->perform($action, $attributes);
        $attributes = $result['attributes'];
        if ($attributes['status'] === self::STATUS_TAKEN) {
            return false;
        }
        if ($attributes['status'] === self::STATUS_AVAILABLE) {
            return true;
        }
        return null;
    }

    /**
     * Suggest.
     * @param string $searchString
     * @param array $tlds
     * @param array $services
     * @return array
     */
    public function suggest($searchString, $tlds, $services = self::SERVICES_SUGGEST)
    {
        $attributes = [
            'searchstring' => $searchString,
            'tlds' => $tlds,
            'services' => $services
        ];
        return $this->perform(self::ACTION_NAME_SUGGEST, $attributes);
    }

    /**
     * Perform query on a domain.
     * @param string $action
     * @param string $query
     * @return array
     */
    public function query(string $action, string $query)
    {
        $attributes = [
            'domain' => $query,
        ];
        return $this->perform($action, $attributes);
    }

    /**
     * Get domains.
     * @param string $domain
     * @param string $type
     * @return array
     * @see https://domains.opensrs.guide/docs/get_domains_by_expiredate
     */
    public function getDomain($domain, $type = 'all_info')
    {
        $action = 'GET';
        $attributes = [
            'type' => $type,
        ];
        $items = [
            'domain' => $domain,
        ];
        return $this->perform($action, $attributes, $items);
    }

    public function getDomainsByExpireDate($time = '+6066 days') {
        $action = 'get_domains_by_expiredate';
        $expiryFrom = date(self::DATE_FORMAT, 1);
        $expiryTo = date(self::DATE_FORMAT, strtotime($time));
        $attributes = [
            'exp_from' => $expiryFrom,
            'exp_to' => $expiryTo,
            'limit' => '9999',
            'page' => 1,
        ];
        return $this->perform($action, $attributes);
    }
}
