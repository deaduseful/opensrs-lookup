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
            if ($transferable === 1) {
                return true;
            }
            if ($transferable === 0) {
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
}
