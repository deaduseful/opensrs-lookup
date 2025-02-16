<?php

namespace Deaduseful\Opensrs;

use RuntimeException;
use SimpleXMLElement;

class RequestBuilder
{

    /** @var float OpenSRS API Version */
    const VERSION = 0.9;

    /** @var string Content Type used in header. */
    const CONTENT_TYPE = 'text/xml';

    /** @var string DocType used by payload. */
    const DOCTYPE = '<!DOCTYPE OPS_envelope SYSTEM "ops.dtd"><OPS_envelope />';

    /**
     * Builds the headers.
     */
    public static function buildHeaders(string $request, string $username, string $key): string
    {
        if (empty($username)) {
            throw new RuntimeException('Missing X-Username: header', 404);
        }
        if (empty($key)) {
            throw new RuntimeException('Missing Key', 404);
        }
        if (empty($request)) {
            throw new RuntimeException('Missing Request', 404);
        }
        $len = strlen($request);
        $signature = self::getSignature($request, $key);
        $header[] = 'Content-Type: ' . self::CONTENT_TYPE;
        $header[] = 'X-Username: ' . $username;
        $header[] = 'X-Signature: ' . $signature;
        $header[] = 'Content-Length: ' . $len;
        return implode(PHP_EOL, $header);
    }

    /**
     * Converts a PHP array into an OPS message.
     */
    public function encode(string $action, array $attributes = [], array $items = []): string
    {
        $markup = self::DOCTYPE;
        $xml = simplexml_load_string($markup);
        $xml->addChild('header')->addChild('version', self::VERSION);
        $assoc = $xml->addChild('body')->addChild('data_block')->addChild('dt_assoc');
        $assoc->addChild('item', 'XCP')->addAttribute('key', 'protocol');
        $assoc->addChild('item', strtoupper($action))->addAttribute('key', 'action');
        $assoc->addChild('item', 'DOMAIN')->addAttribute('key', 'object');
        foreach ($items as $key => $item) {
            $assoc->addChild('item', $item)->addAttribute('key', $key);
        }
        $attributesItem = $assoc->addChild('item');
        $attributesItem->addAttribute('key', 'attributes');
        $this->parseAttributes($attributes, $attributesItem);
        return $xml->asXML();
    }

    private function parseAttributes(array $attributes, SimpleXMLElement $attributesItem): void
    {
        $indexType = array_values($attributes) === $attributes ? 'dt_array' : 'dt_assoc';
        $attributesAssoc = $attributesItem->addChild($indexType);
        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $item = $attributesAssoc->addChild('item');
                $item->addAttribute('key', $key);
                self::parseAttributes($value, $item);
            } else {
                $attributesAssoc->addChild('item', $value)->addAttribute('key', $key);
            }
        }
    }

    private static function getSignature(string $request, string $key): string
    {
        return md5(md5($request . $key) . $key);
    }
}
