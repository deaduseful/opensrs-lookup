<?php

namespace Deaduseful\Opensrs;

/**
 * OpenSRS reseller username.
 */
defined('OSRS_USERNAME') || define('OSRS_USERNAME', (string)getenv('OSRS_USERNAME'));

/**
 * OpenSRS reseller private Key. Please generate a key if you do not already have one.
 */
defined('OSRS_KEY') || define('OSRS_KEY', (string)getenv('OSRS_KEY'));

class Lookup
{
    /**
     * @const string LIVE OpenSRS domain service API host.
     */
    const LIVE_HOST = 'https://rr-n1-tor.opensrs.net:55443';

    /**
     * @const string TEST OpenSRS domain service API host.
     */
    const TEST_HOST = 'https://horizon.opensrs.net:55443';

    /**
     * @const string OpenSRS reseller username.
     */
    const USERNAME = OSRS_USERNAME;

    /**
     * @const string OpenSRS reseller private Key. Please generate a key if you do not already have one.
     */
    const KEY = OSRS_KEY;

    const ACTION_CHECK_TRANSFER = 'check_transfer';
    const ACTION_LOOKUP = 'lookup';
    const ACTION_NAME_SUGGEST = 'name_suggest';
    const SERVICES_SUGGEST = ['lookup', 'suggestion', 'premium', 'personal_names'];
    const STATUS_AVAILABLE = 'available';
    const STATUS_TAKEN = 'taken';
    const STATUS_TRANSFERRABLE = 'transferrable';

    /**
     * @var int
     */
    private $timeout = Request::SOCKET_TIMEOUT;
    /**
     * @var string
     */
    private $host = self::LIVE_HOST;
    /**
     * @var string
     */
    private $username = self::USERNAME;
    /**
     * @var string
     */
    private $key = self::KEY;

    /**
     * Lookup constructor.
     * @param string $username
     * @param string $key
     * @param bool $test
     */
    public function __construct(string $username = self::USERNAME, string $key = self::KEY, bool $test = false)
    {
        $host = $test ? self::TEST_HOST : self::LIVE_HOST;
        $this->setUsername($username)->setKey($key)->setHost($host);
    }

    /**
     * @param string $query
     * @return bool|null
     */
    public function checkTransfer(string $query)
    {
        $attributes = ['domain' => $query];
        $result = $this->perform(self::ACTION_CHECK_TRANSFER, $attributes);
        $attributes = $result['attributes'];
        $key = self::STATUS_TRANSFERRABLE;
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
     * Perform action.
     * @param string $action
     * @param array $attributes
     * @return array
     */
    private function perform(string $action = self::ACTION_LOOKUP, array $attributes = [])
    {
        $username = $this->getUsername();
        $key = $this->getKey();
        $host = $this->getHost();
        $timeout = $this->getTimeout();
        $result = self::getResult($action, $attributes, $username, $key, $host, $timeout);
        return $result;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return Lookup
     */
    public function setUsername(string $username): Lookup
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Lookup
     */
    public function setKey(string $key): Lookup
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return Lookup
     */
    public function setHost(string $host): Lookup
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return Lookup
     */
    public function setTimeout(int $timeout): Lookup
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @param string $action
     * @param array $attributes
     * @param string $username
     * @param string $key
     * @param string $host
     * @param int $timeout
     * @return array
     */
    private static function getResult(string $action = self::ACTION_LOOKUP, array $attributes = [], string $username = self::USERNAME, string $key = self::KEY, string $host = self::LIVE_HOST, int $timeout = self::SOCKET_TIMEOUT): array
    {
        $request = Request::encode($action, $attributes);
        $headers = Request::buildHeaders($request, $username, $key);
        $contents = Request::filePostContents($host, $request, $headers, $timeout);
        $responseHeaders = Request::getResponseHeaders();
        $content = Response::parseContents($contents, $responseHeaders);
        Response::checkContent($content, $host, $request, $headers, $responseHeaders);
        $result = Response::formatResult($content);
        return $result;
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
