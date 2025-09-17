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
    protected const LIVE_HOST = 'https://rr-n1-tor.opensrs.net:55443';

    /**
     * @const string TEST OpenSRS domain service API host.
     */
    protected const TEST_HOST = 'https://horizon.opensrs.net:55443';

    /**
     * @const string OpenSRS reseller username.
     */
    protected const USERNAME = OSRS_USERNAME;

    /**
     * @const string OpenSRS reseller private Key. Please generate a key if you do not already have one.
     */
    protected const KEY = OSRS_KEY;

    /**
     * @const string Default for the test flag.
     */
    protected const TEST = false;

    protected int $timeout = RequestClient::SOCKET_TIMEOUT;
    protected string $host = self::LIVE_HOST;
    protected string $username = self::USERNAME;
    protected string $key = self::KEY;
    protected RequestClient $requestClient;
    protected RequestBuilder $requestBuilder;
    protected ResponseParser $responseParser;
    private ?Result $result;

    /**
     * Service constructor.
     */
    public function __construct(
        string $username = self::USERNAME,
        string $key = self::KEY,
        bool $test = self::TEST,
        RequestBuilder $requestBuilder = null,
        RequestClient $requestClient = null,
        ResponseParser $responseParser = null
    ) {
        $host = $test ? self::TEST_HOST : self::LIVE_HOST;
        $this->setUsername($username)->setKey($key)->setHost($host);
        $this->requestBuilder = $requestBuilder ?: new RequestBuilder();
        $this->requestClient = $requestClient ?: new RequestClient();
        $this->responseParser = $responseParser ?: new ResponseParser();
    }

    /**
     * Perform action.
     */
    public function perform(string $action, array $attributes = [], array $items = []): array
    {
        return $this->parseResult($action, $attributes, $items)->getResult()->toArray();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    protected function parseResult(string $action, array $attributes = [], array $items = []): self
    {
        $request = $this->requestBuilder->encode($action, $attributes, $items);
        $content = $this->getContents($request);
        $this->result = $this->responseParser
            ->checkContent($content)
            ->parseResult($content)
            ->getResult();
        return $this;
    }

    protected function getContents(string $request): string
    {
        $username = $this->getUsername();
        $key = $this->getKey();
        $host = $this->getHost();
        $timeout = $this->getTimeout();
        $headers = $this->requestBuilder->buildHeaders($request, $username, $key);
        $this->requestClient->call($request, $headers, $host, $timeout);
        $contents = $this->requestClient->getContents();
        $responseHeaders = $this->requestClient->getHeaders();
        return $this->responseParser->parseContents($contents, $responseHeaders);
    }

    public function getResult(): ?Result
    {
        return $this->result;
    }
}
