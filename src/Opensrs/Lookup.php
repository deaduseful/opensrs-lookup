<?php

namespace Deaduseful\Opensrs;

use DomainException;
use Exception;
use InvalidArgumentException;
use SimpleXMLElement;

/**
 * OpenSRS reseller username.
 */
define('OSRS_USERNAME', (string)getenv('OSRS_USERNAME'));

/**
 * OpenSRS reseller private Key. Please generate a key if you do not already have one.
 */
define('OSRS_KEY', (string)getenv('OSRS_KEY'));

class Lookup
{
    /**
     * OpenSRS domain service API url.
     * LIVE => rr-n1-tor.opensrs.net, TEST => horizon.opensrs.net
     */
    const HOST = 'https://rr-n1-tor.opensrs.net:55443';

    /**
     * OpenSRS reseller username.
     */
    const USERNAME = OSRS_USERNAME;

    /**
     * OpenSRS reseller private Key. Please generate a key if you do not already have one.
     */
    const KEY = OSRS_KEY;

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
    private $host = self::HOST;

    /**
     * @var string
     */
    private $username = OSRS_USERNAME;

    /**
     * @var string
     */
    private $key = OSRS_KEY;

    public $request;
    public $headers;
    public $content;

    /**
     * @param $query
     * @return bool
     * @throws Exception
     */
    public function checkTransfer($query)
    {
        $result = $this->lookup($query, 'check_transfer');
        return (int)$result['transferrable'] === 1;
    }

    /**
     * Perform lookup.
     * @param string $query
     * @param string $action
     * @param string $username
     * @param string $key
     * @return array
     * @throws Exception
     */
    public function lookup(string $query, string $action = 'lookup', string $username = self::USERNAME, string $key = self::KEY)
    {
        $this->setQuery($query);
        $this->setAction($action);
        $this->setUsername($username);
        $this->setKey($key);
        $this->process();
        return $this->getResult();
    }

    /**
     * Process query.
     * @throws DomainException
     * @throws Exception
     */
    private function process()
    {
        $this->request = $this->encode();
        $this->headers = $this->buildHeaders($this->request);
        $this->content = $this->filePostContents($this->getHost(), $this->request, $this->headers);
        $xml = simplexml_load_string($this->content, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (is_object($xml) === false) {
            throw new DomainException('Invalid XML response.');
        }
        $dataBlock = [];
        foreach ($xml->body->data_block->dt_assoc->item as $item) {
            $key = (string)$item->attributes()['key'];
            $value = $item;
            $dataBlock[$key] = $value;
        }
        $responseCode = (int)$dataBlock['response_code'];
        if ($responseCode === 401) {
            throw new DomainException('Username or key is incorrect, please check your config file.');
        }
        if ($responseCode > 299) {
            throw new DomainException($dataBlock['response_text']);
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
    public function setAction(string $action)
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
    public function setQuery(string $query)
    {
        $this->query = $query;
    }

    /**
     * Builds the headers.
     *
     * @param string $request
     * @return string
     * @throws InvalidArgumentException
     */
    private function buildHeaders(string $request)
    {
        if (empty($this->getUsername())) {
            throw new InvalidArgumentException('Username cannot be empty');
        }
        if (empty($this->getKey())) {
            throw new InvalidArgumentException('Key cannot be empty');
        }
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
     * Similar to file_get_contents but uses the POST method.
     *
     * @param string $host
     * @param string $content
     * @param string $headers
     * @return string
     * @throws Exception
     * @throws DomainException
     */
    private function filePostContents(string $host, string $content, string $headers)
    {
        $options = [
            'http' =>
                [
                    'method' => 'POST',
                    'header' => $headers,
                    'content' => $content,
                    'timeout' => $this->getTimeout()
                ]
        ];
        $context = stream_context_create($options);
        $flags = null;
        $contents = @file_get_contents($host, $flags, $context);
        if (empty($this->content)) {
            throw new DomainException(sprintf('Empty response, from host %s, with request options %s, response headers: %s', $host, var_export($options, true)), var_export($http_response_header, true));
        }
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
