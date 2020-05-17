<?php

namespace Deaduseful\Opensrs;

use DomainException;
use RuntimeException;
use SimpleXMLElement;
use UnexpectedValueException;

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
     * @const string The Closing Ops Envelope string.
     */
    const OPS_ENVELOPE = '</OPS_envelope>';

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
    private $timeout = self::SOCKET_TIMEOUT;
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
        $request = self::encode($action, $attributes);
        $headers = self::buildHeaders($request, $username, $key);
        $contents = self::filePostContents($host, $request, $headers, $timeout);
        $responseHeaders = self::getResponseHeaders();
        $content = self::parseContents($contents, $responseHeaders);
        self::checkContent($content, $host, $request, $headers, $responseHeaders);
        $result = self::formatResult($content);
        return $result;
    }

    /**
     * Converts a PHP array into an OPS message.
     * @param string $action
     * @param array $attributes
     * @param string $object
     * @return string OPS XML message.
     */
    public static function encode(string $action = self::ACTION_LOOKUP, array $attributes = [], string $object = 'DOMAIN')
    {
        $markup = '<!DOCTYPE OPS_envelope SYSTEM "ops.dtd"><OPS_envelope></OPS_envelope>';
        $xml = new SimpleXMLElement($markup);
        $assoc = $xml->addChild('body')->addChild('data_block')->addChild('dt_assoc');
        $assoc->addChild('item', 'XCP')->addAttribute('key', 'protocol');
        $assoc->addChild('item', $action)->addAttribute('key', 'action');
        $assoc->addChild('item', $object)->addAttribute('key', 'object');
        $attributesItem = $assoc->addChild('item');
        $attributesItem->addAttribute('key', 'attributes');
        $attributesAssoc = $attributesItem->addChild('dt_assoc');
        foreach ($attributes as $key => $value) {
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
     * Builds the headers.
     *
     * @param string $request
     * @param string $username
     * @param string $key
     * @return string
     */
    private static function buildHeaders(string $request, $username, $key)
    {
        $len = strlen($request);
        $signature = md5(md5($request . $key) . $key);
        $header[] = 'Content-Type: text/xml';
        $header[] = 'X-Username: ' . $username;
        $header[] = 'X-Signature: ' . $signature;
        $header[] = 'Content-Length: ' . $len;
        $headers = implode(PHP_EOL, $header);
        return $headers;
    }

    /**
     * Similar to file_get_contents but uses the POST method.
     *
     * @param string $host
     * @param string $content
     * @param string $headers
     * @param int $timeout
     * @return string
     */
    private static function filePostContents(string $host, string $content, string $headers, int $timeout = self::SOCKET_TIMEOUT)
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
                    'timeout' => $timeout
                ]
        ];
        $context = stream_context_create($options);
        $flags = null;
        $responseContent = file_get_contents($host, $flags, $context);
        return $responseContent;
    }

    /**
     * @return array
     */
    public static function getResponseHeaders(): array
    {
        $responseHeaders = isset($http_response_header) ? $http_response_header : [];
        return $responseHeaders;
    }

    /**
     * @param string $contents
     * @param array $responseHeaders
     * @return string
     */
    private static function parseContents($contents, $responseHeaders)
    {
        if (empty($contents)) {
            if (empty($responseHeaders) === false) {
                $contents = implode(PHP_EOL, $responseHeaders);
                if (strpos($contents, self::OPS_ENVELOPE) === false) {
                    $contents .= self::OPS_ENVELOPE;
                }
            }
        }
        return $contents;
    }

    /**
     * @param string $content
     * @param string $host
     * @param string $request
     * @param string $headers
     * @param array $responseHeaders
     */
    private static function checkContent(string $content, string $host, string $request, string $headers, array $responseHeaders): void
    {
        if (empty($content)) {
            throw new DomainException(
                sprintf(
                    'Empty response, from host %s, with request content %s, request headers %s response headers: %s',
                    $host,
                    var_export($request, true),
                    var_export($headers, true),
                    var_export($responseHeaders, true)
                )
            );
        }
    }

    /**
     * @param string $content
     * @return array
     */
    public static function formatResult($content): array
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
                $value = self::parseItem($item);
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
    private static function parseItem(SimpleXMLElement $item)
    {
        if (isset($item->dt_assoc) || isset($item->dt_array)) {
            $value = [];
            $array = isset($item->dt_assoc->item) ? $item->dt_assoc->item : $item->dt_array->item;
            foreach ($array as $subItem) {
                $key = (string)$subItem->attributes()['key'];
                $value[$key] = self::parseItem($subItem);
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
