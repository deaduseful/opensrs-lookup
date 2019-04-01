<?php

namespace Deaduseful\Opensrs;

use Exception;
use SimpleXMLElement;

/**
 * OpenSRS reseller username.
 */
define('OSRS_USERNAME', (string) getenv('OSRS_USERNAME'));

/**
 * OpenSRS reseller private Key. Please generate a key if you do not already have one.
 */
define('OSRS_KEY', (string) getenv('OSRS_KEY'));

class Lookup
{
    /**
     * OpenSRS domain service API url.
     * LIVE => rr-n1-tor.opensrs.net, TEST => horizon.opensrs.net
     */
    const OSRS_HOST = 'https://rr-n1-tor.opensrs.net:55443';

    /**
     * Socket Timeout in seconds.
     */
    const SOCKET_TIMEOUT = 120;

    /**
     * @var string
     */
    private $action = '';

    /**
     * @var string
     */
    private $query = '';

    /**
     * @var array
     */
    private $result = [];

    /**
     * @var int
     */
    private $timeout = self::SOCKET_TIMEOUT;

    /**
     * @var string
     */
    private $host = self::OSRS_HOST;

    /**
     * @var string
     */
    private $username = OSRS_USERNAME;

    /**
     * @var string
     */
    private $key = OSRS_KEY;

    /**
     * DomainTransferable constructor.
     * @param string $query
     * @param string $action
     * @param string $username
     * @param string $key
     */
    public function __construct($query, $action = 'lookup', $username = OSRS_USERNAME, $key = OSRS_KEY)
    {
        $this->setQuery($query);
        $this->setAction($action);
        $this->setUsername($username);
        $this->setKey($key);
        $this->process();
    }

    /**
     * Process query.
     * @throws Exception
     */
    private function process()
    {
        $request = $this->encode();
        $headers = $this->buildHeaders($request);
        $result = $this->filePostContents($this->getHost(), $request, $headers);
        if (empty($result)) {
            throw new Exception('Empty response.');
        }
        $xml = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (is_object($xml) === false) {
            throw new Exception('Unable to load XML response.');
        }
        $dataBlock = [];
        foreach ($xml->body->data_block->dt_assoc->item as $item) {
            $key = (string)$item->attributes()['key'];
            $value = $item;
            $dataBlock[$key] = $value;
        }
        $responseCode = (int)$dataBlock['response_code'];
        if ($responseCode === 401) {
            throw new Exception('Username or key is incorrect, please check your config file.');
        }
        if ($responseCode > 299) {
            throw new Exception($dataBlock['response_text']);
        }
        $attributes = [];
        foreach ($dataBlock['attributes']->dt_assoc->item as $item) {
            $key = (string)$item->attributes()['key'];
            $value = (string)$item;
            $attributes[$key] = $value;
        }
        $this->setResult($attributes);
    }

    /**
     * Converts a PHP array into an OPS message.
     * @return string OPS XML message.
     */
    function encode()
    {
        $xml = new SimpleXMLElement('<!DOCTYPE OPS_envelope SYSTEM "ops.dtd"><OPS_envelope></OPS_envelope>');
        $assoc = $xml->addChild('body')->addChild('data_block')->addChild('dt_assoc');
        $assoc->addChild('item', 'XCP')->addAttribute('key', 'protocol');
        $assoc->addChild('item', $this->getAction())->addAttribute('key', 'action');
        $assoc->addChild('item', 'DOMAIN')->addAttribute('key', 'object');
        $attributes = $assoc->addChild('item');
        $attributesAssoc = $attributes->addChild('dt_assoc');
        $attributesAssoc->addChild('item', $this->getQuery())->addAttribute('key', 'domain');
        $attributes->addAttribute('key', 'attributes');
        $request = $xml->asXML();
        return $request;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Builds the headers.
     *
     * @param string $request
     * @return string
     */
    private function buildHeaders($request)
    {
        $len = strlen($request);
        $signature = md5(md5($request . $this->getKey()) . $this->getKey());
        $header[] = 'Content-Type: text/xml';
        $header[] = 'X-Username: ' . $this->getUsername();
        $header[] = 'X-Signature: ' . $signature;
        $header[] = 'Content-Length: ' . $len;
        $headers = implode(PHP_EOL, $header);
        return $headers;
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
     * Similar to file_get_contents but uses the POST method.
     *
     * @param $host
     * @param $content
     * @param $headers
     * @return string
     * @throws Exception
     */
    private function filePostContents($host, $content, $headers)
    {
        $opts = [
            'http' =>
                [
                    'method' => 'POST',
                    'header' => $headers,
                    'content' => $content,
                    'timeout' => $this->getTimeout()
                ]
        ];
        $context = stream_context_create($opts);
        $flags = null;
        $contents = @file_get_contents($host, $flags, $context);
        return $contents;
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
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array $result
     * @return Lookup
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }
}
