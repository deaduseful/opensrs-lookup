<?php

namespace Deaduseful\Opensrs;

use RuntimeException;
use SimpleXMLElement;

class Request
{
    /**
     * @const int Socket Timeout in seconds.
     */
    const SOCKET_TIMEOUT = 120;

    /**
     * @return array
     */
    public static function getResponseHeaders(): array
    {
        return isset($http_response_header) ? $http_response_header : [];
    }

    /**
     * Similar to file_get_contents but uses the POST method.
     *
     * @param string $url
     * @param string $content
     * @param string $headers
     * @param int $timeout
     * @return string
     */
    public static function filePostContents(string $url, string $content, string $headers, int $timeout = self::SOCKET_TIMEOUT)
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
        return self::fileGetContents($url, $options);
    }

    /**
     * @param string $filename
     * @param array $options
     * @return false|string
     */
    public static function fileGetContents(string $filename, $options = []) {
        $context = stream_context_create($options);
        return file_get_contents($filename, false, $context);
    }

    /**
     * Builds the headers.
     *
     * @param string $request
     * @param string $username
     * @param string $key
     * @return string
     */
    public static function buildHeaders(string $request, string $username, string $key)
    {
        $len = strlen($request);
        $signature = md5(md5($request . $key) . $key);
        $header[] = 'Content-Type: text/xml';
        $header[] = 'X-Username: ' . $username;
        $header[] = 'X-Signature: ' . $signature;
        $header[] = 'Content-Length: ' . $len;
        return implode(PHP_EOL, $header);
    }

    /**
     * Converts a PHP array into an OPS message.
     * @param string $action
     * @param array $attributes
     * @param array $items
     * @return string OPS XML message.
     */
    public static function encode($action, array $attributes = [], array $items = [])
    {
        $markup = '<!DOCTYPE OPS_envelope SYSTEM "ops.dtd"><OPS_envelope></OPS_envelope>';
        $xml = new SimpleXMLElement($markup);
        $assoc = $xml->addChild('body')->addChild('data_block')->addChild('dt_assoc');
        $assoc->addChild('item', 'XCP')->addAttribute('key', 'protocol');
        $assoc->addChild('item', $action)->addAttribute('key', 'action');
        $assoc->addChild('item', 'DOMAIN')->addAttribute('key', 'object');
        foreach ($items as $key => $item) {
            $assoc->addChild('item', $item)->addAttribute('key', $key);
        }
        $attributesItem = $assoc->addChild('item');
        $attributesItem->addAttribute('key', 'attributes');
        $attributesAssoc = $attributesItem->addChild('dt_assoc');
        self::parseAttributes($attributes, $attributesAssoc);
        return $xml->asXML();
    }

    /**
     * @param array $attributes
     * @param SimpleXMLElement $attributesAssoc
     */
    private static function parseAttributes(array $attributes, SimpleXMLElement $attributesAssoc): void
    {
        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $item = $attributesAssoc->addChild('item');
                $item->addAttribute('key', $key);
                $attributesArray = $item->addChild('dt_array');
                foreach ($value as $arrayKey => $arrayValue) {
                    self::parseAttributes($arrayValue, $attributesArray);
                }
            } else {
                $attributesAssoc->addChild('item', $value)->addAttribute('key', $key);
            }
        }
    }
}
