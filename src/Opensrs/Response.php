<?php

namespace Deaduseful\Opensrs;

use DomainException;
use SimpleXMLElement;
use UnexpectedValueException;

class Response
{
    /**
     * @const string[] Response codes and their status.
     */
    const RESPONSE_CODES = [
        200 => 'success',
        400 => 'invalid_credentials',
        401 => 'unauthorized',
        404 => 'missing_header',
        480 => 'missing_expiration_year', // "Current expiration year must be specified"
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
     * @param string $content
     * @param string $host
     * @param string $request
     * @param string $headers
     * @param array $responseHeaders
     */
    public static function checkContent(string $content, string $host, string $request, string $headers, array $responseHeaders): void
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
     * @param string $contents
     * @param array $responseHeaders
     * @return string
     */
    public static function parseContents($contents, $responseHeaders)
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
}