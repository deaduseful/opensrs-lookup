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
        $responseHeaders = isset($http_response_header) ? $http_response_header : [];
        return $responseHeaders;
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
    public static function filePostContents(string $host, string $content, string $headers, int $timeout = self::SOCKET_TIMEOUT)
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
     * Builds the headers.
     *
     * @param string $request
     * @param string $username
     * @param string $key
     * @return string
     */
    public static function buildHeaders(string $request, $username, $key)
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
     * Converts a PHP array into an OPS message.
     * @param string $action
     * @param array $attributes
     * @param string $object
     * @return string OPS XML message.
     */
    public static function encode(string $action, array $attributes = [], string $object = 'DOMAIN')
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
}
