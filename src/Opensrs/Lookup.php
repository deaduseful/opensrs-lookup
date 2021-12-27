<?php

namespace Deaduseful\Opensrs;

class Lookup extends Service
{

    public const ACTION_CHECK_TRANSFER = 'check_transfer';
    public const ACTION_LOOKUP = 'lookup';
    public const ACTION_NAME_SUGGEST = 'name_suggest';
    public const SERVICES_SUGGEST = ['lookup', 'suggestion', 'premium', 'personal_names'];
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_TAKEN = 'taken';
    public const STATUS_TRANSFER = 'transferrable';
    public const DATE_FORMAT = 'Y-m-d';

    /**
     * @param string $query
     * @return bool|null
     */
    public function checkTransfer(string $query): ?bool
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
    public function lookup(string $query, string $action = self::ACTION_LOOKUP): array
    {
        $attributes = ['domain' => $query];
        return $this->perform($action, $attributes);
    }

    /**
     * @param string $query
     * @return bool|null
     */
    public function available(string $query): ?bool
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
    public function suggest(string $searchString, array $tlds, array $services = self::SERVICES_SUGGEST): array
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
    public function query(string $action, string $query): array
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
    public function getDomain(string $domain, string $type = 'all_info'): array
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

    public function getDomainsByExpireTime($toTime = 2147483647 - 86400, $fromTime = 1, $limit = 999999999, $page = 1): array
    {
        $expiryFrom = date(self::DATE_FORMAT, $fromTime);
        $expiryTo = date(self::DATE_FORMAT, $toTime);
        return $this->getDomainsByExpireDate($expiryTo, $expiryFrom, $limit, $page);
    }

    public function getDomainsByExpireDate($expiryTo = '2038-01-18', $expiryFrom = '1970-01-01', $limit = 999999999, $page = 1): array
    {
        $action = 'get_domains_by_expiredate';
        $attributes = [
            'exp_from' => $expiryFrom,
            'exp_to' => $expiryTo,
            'limit' => $limit,
            'page' => $page,
        ];
        return $this->perform($action, $attributes);
    }
}
