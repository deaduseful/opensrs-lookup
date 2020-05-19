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

class Service
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
     * Service constructor.
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
     * Perform action.
     * @param string $action
     * @param array $attributes
     * @param array $items
     * @return array
     */
    public function perform(string $action, array $attributes = [], $items = [])
    {
        $result = $this->getResult($action, $attributes, $items);
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
     * @return self
     */
    public function setUsername(string $username): self
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
     * @return self
     */
    public function setKey(string $key): self
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
     * @return self
     */
    public function setHost(string $host): self
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
     * @return self
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @param string $action
     * @param array $attributes
     * @param array $items
     * @return array
     */
    public function getResult(string $action, array $attributes = [], array $items = []): array
    {
        $request = Request::encode($action, $attributes, $items);
        $username = $this->getUsername();
        $key = $this->getKey();
        $host = $this->getHost();
        $timeout = $this->getTimeout();
        $headers = Request::buildHeaders($request, $username, $key);
        $contents = Request::filePostContents($host, $request, $headers, $timeout);
        $responseHeaders = Request::getResponseHeaders();
        $content = Response::parseContents($contents, $responseHeaders);
        Response::checkContent($content, $host, $request, $headers, $responseHeaders);
        $result = Response::formatResult($content);
        return $result;
    }

}
