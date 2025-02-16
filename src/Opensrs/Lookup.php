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
    public const TYPE_ALL = 'all_info';
    public const TYPES = [
        'admin', // Returns admin contact information.
        'all_info', // Returns all information.
        'billing', // Returns billing contact information.
        'ca_whois_display_setting', // Returns the current CIRA Whois Privacy setting for .CA domains.
        'domain_auth_info', // Returns domain authorization code, if applicable.
        'expire_action', // Returns the action to be taken upon domain expiry, specifically whether to auto-renew the domain, or let it expire silently.
        'forwarding_email', // Returns forwarding email for .NAME 2nd level.
        'list', // Returns list of domains in the same profile or returns list of domains for user using cookie method.
        'nameservers', // Returns nameserver information.
        'owner', // Returns owner contact information.
        'rsp_whois_info', // Returns name and contact information for RSP.
        'status', // Returns lock or escrow status of the domain.
        'tech', // Returns tech contact information.
        'tld_data', // Returns additional information that is required by some registries, such as the residency of the registrant.
        'waiting history', // Returns information on asynchronous requests.
        'whois_privacy_state', // Returns the state for the WHOIS Privacy feature: enabled, disabled, enabling, or disabling. Note: If the TLD does not allow WHOIS Privacy, always returns Disabled.
        'whois_publicity_state', // Returns the state for the WHOIS Publicity feature: enabled, disabled. Note: If the TLD does not allow WHOIS Privacy, always returns Disabled.
        'xpack_waiting_history', // Returns the state of completed/cancelled requests not yet deleted from the database for .DK domains. All completed/cancelled requests are deleted from the database two weeks after they move to final state.
    ];

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
     */
    public function lookup(string $query, string $action = self::ACTION_LOOKUP): array
    {
        $attributes = ['domain' => $query];
        return $this->perform($action, $attributes);
    }

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
     * @see https://domains.opensrs.guide/docs/get_domains_by_expiredate
     */
    public function getDomain(string $domain, string $type = self::TYPE_ALL): array
    {
        $this->checkTypes($type);
        $action = 'GET';
        $attributes = [
            'type' => $type,
            'domain_name' => $domain,
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

    protected function checkTypes(string $type): void
    {
        if (in_array($type, self::TYPES) === false) {
            $message = sprintf('Type expected %s, got %s', implode(', ', self::TYPES), $type);
            throw new \InvalidArgumentException($message);
        }
    }
}
