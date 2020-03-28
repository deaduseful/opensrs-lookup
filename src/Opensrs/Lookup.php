<?php

namespace Deaduseful\Opensrs;

use DomainException;
use InvalidArgumentException;
use RuntimeException;
use SimpleXMLElement;
use UnexpectedValueException;

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
     * @const string LIVE OpenSRS domain service API host.
     */
    const LIVE_HOST = 'https://rr-n1-tor.opensrs.net:55443';

    /**
     * @const string TEST OpenSRS domain service API host.
     */
    const TEST_HOST = 'https://horizon.opensrs.net:55443';

    /**
     * @const string OpenSRS domain service API host.
     */
    const HOST = self::LIVE_HOST;

    /**
     * @const string OpenSRS reseller username.
     */
    const USERNAME = OSRS_USERNAME;

    /**
     * @const string OpenSRS reseller private Key. Please generate a key if you do not already have one.
     */
    const KEY = OSRS_KEY;

    /**
     * @const int Socket Timeout in seconds.
     */
    const SOCKET_TIMEOUT = 120;

    /**
     * @const string[] Response codes and their status.
     */
    const RESPONSE_CODES = [
        200 => 'success',
        400 => 'invalid_credentials',
        401 => 'unauthorized',
        404 => 'missing_header',
        555 => 'invalid_ip'
    ];

    /**
     * @const string Unknown status.
     */
    const STATUS_UNKNOWN = 'unknown';

    /**
     * @var string
     */
    public $responseContent;
    /**
     * @var array
     */
    public $responseHeaders = [];
    /**
     * @var string
     */
    private $request = '';
    /**
     * @var string
     */
    private $headers;
    /**
     * @var string
     */
    private $action = '';
    /**
     * @var array
     */
    private $attributes = [];
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
    private $username = self::USERNAME;
    /**
     * @var string
     */
    private $key = self::KEY;
    /**
     * @var string
     */
    private $content;

    /**
     * Lookup constructor.
     * @param string $username
     * @param string $key
     * @param bool $test
     */
    public function __construct(string $username = self::USERNAME, string $key = self::KEY, bool $test = false)
    {
        $host = $test ? self::TEST_HOST : self::LIVE_HOST;
        $this->setUsername($username)
            ->setKey($key)
            ->setHost($host);
    }

    /**
     * @param string $query
     * @return bool|null
     */
    public function checkTransfer(string $query)
    {
        $this->attributes['domain'] = $query;
        $result = $this->perform('check_transfer')->getResult();
        $attributes = $result['attributes'];
        if (array_key_exists('transferrable', $attributes)) {
            return (int)$attributes['transferrable'] === 1;
        }
        return null;
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

    /**
     * Perform action.
     * @param string $action
     * @return Lookup
     * @throws DomainException If content is empty.
     */
    private function perform(string $action = 'lookup')
    {
        $this->setAction($action);
        $this->request = $this->encode();
        $this->headers = $this->buildHeaders($this->request);
        $host = $this->getHost();
        $contents = $this->filePostContents($host, $this->request, $this->headers);
        $this->content = $this->parseContents($contents);
        $this->checkContent();
        $result = $this->formatResult($this->content);
        return $this->setResult($result);
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
        $attributes->addAttribute('key', 'attributes');
        $attributesAssoc = $attributes->addChild('dt_assoc');
        foreach ($this->getAttributes() as $key => $value) {
            if (is_array($value)) {
                $item = $attributesAssoc->addChild('item');
                $item->addAttribute('key', $key);
                $attributesArray = $item->addChild('dt_array');
                foreach ($value as $arrayKey => $arrayValue) {
                    $attributesArray->addChild('item', $arrayValue)->addAttribute('key', $arrayKey);
                }
            } else {
                $attributesAssoc->addChild('item', $value)->addAttribute('key', $key);
            }
        }
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
     * @return Lookup
     */
    public function setAction(string $action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return Lookup
     */
    public function setAttributes(array $attributes): Lookup
    {
        $this->attributes = $attributes;
        return $this;
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
     * Similar to file_get_contents but uses the POST method.
     *
     * @param string $host
     * @param string $content
     * @param string $headers
     * @return string
     * @throws DomainException
     */
    private function filePostContents(string $host, string $content, string $headers)
    {
        if (ini_get('allow_url_fopen') == '0') {
            throw new RuntimeException('Disabled in the server configuration by allow_url_fopen=0');
        }
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
        $this->responseContent = file_get_contents($host, $flags, $context);
        $this->responseHeaders = isset($http_response_header) ? $http_response_header : [];
        return $this->responseContent;
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
     * @param string $contents
     * @return string
     */
    private function parseContents($contents)
    {
        $responseHeaders = $this->responseHeaders;
        if (empty($contents)) {
            if (empty($responseHeaders) === false) {
                $contents = implode(PHP_EOL, $responseHeaders);
                if (strpos($contents, '</OPS_envelope>') === false) {
                    $contents .= '</OPS_envelope>';
                }
            }
        }
        return $contents;
    }

    /**
     * Check Content.
     */
    private function checkContent(): void
    {
        if (empty($this->content)) {
            throw new DomainException(
                sprintf(
                    'Empty response, from host %s, with request content %s, request headers %s response headers: %s',
                    $this->getHost(),
                    var_export($this->request, true),
                    var_export($this->headers, true),
                    var_export($this->responseHeaders, true)
                )
            );
        }
    }

    /**
     * @param string $content
     * @return array
     */
    public function formatResult($content): array
    {
        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (is_object($xml) === false) {
            throw new UnexpectedValueException('Invalid XML response.');
        }
        $dataBlock = [];
        foreach ($xml->body->data_block->dt_assoc->item as $item) {
            $key = (string)$item->attributes()['key'];
            $value = $item;
            $dataBlock[$key] = $value;
        }
        $responseCode = (int)$dataBlock['response_code'];
        $responseCodes = self::RESPONSE_CODES;
        if (isset($responseCodes[$responseCode]) === true) {
            $status = $responseCodes[$responseCode];
        } else {
            $status = self::STATUS_UNKNOWN;
        }
        $attributes = [];
        if (array_key_exists('attributes', $dataBlock)) {
            foreach ($dataBlock['attributes']->dt_assoc->item as $item) {
                $key = (string)$item->attributes()['key'];
                $value = $this->parseItem($item);
                $attributes[$key] = $value;
            }
        }
        $response = (string)$dataBlock['response_text'];
        $result = [
            'response' => $response,
            'code' => $responseCode,
            'status' => $status,
            'attributes' => $attributes
        ];
        return $result;
    }

    /**
     * @param SimpleXMLElement $item
     * @return array|string
     */
    private function parseItem(SimpleXMLElement $item)
    {
        if (isset($item->dt_assoc) || isset($item->dt_array)) {
            $value = [];
            $array = isset($item->dt_assoc->item) ? $item->dt_assoc->item : $item->dt_array->item;
            foreach ($array as $subItem) {
                $key = (string)$subItem->attributes()['key'];
                $value[$key] = $this->parseItem($subItem);
            }
        } else {
            $value = (string)$item;
        }
        return $value;
    }

    /**
     * Perform lookup.
     * @param string $query
     * @param string $action
     * @return array
     */
    public function lookup(string $query, string $action = 'lookup')
    {
        $this->attributes['domain'] = $query;
        return $this->perform($action)->getResult();
    }

    /**
     * @param string $query
     * @return bool
     */
    public function available(string $query)
    {
        $this->attributes['domain'] = $query;
        $result = $this->perform()->getResult();
        $attributes = $result['attributes'];
        if ($attributes['status'] === 'taken') {
            return false;
        }
        if ($attributes['status'] === 'available') {
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
    public function suggest($searchString, $tlds, $services = ['lookup', 'suggestion', 'premium', 'personal_names'])
    {
        $attributes = [
            'searchstring' => $searchString,
            'tlds' => $tlds,
            'services' => $services
        ];
        $this->attributes = $attributes;
        return $this->perform('name_suggest')->getResult();
    }

    /**
     * @param string $key
     * @param string $value
     * @return Lookup
     */
    public function setAttribute(string $key, string $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeaders(): string
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getRequest(): string
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
