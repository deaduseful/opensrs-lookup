<?php

namespace Deaduseful\Opensrs;

use DomainException;
use SimpleXMLElement;
use UnexpectedValueException;

class Response
{
    /**
     * @see https://domains.opensrs.guide/docs/codes
     * @const string[] Response codes and their status.
     */
    const RESPONSE_CODES = [
        self::CODE_UNKNOWN => self::STATUS_UNKNOWN,
        self::CODE_SUCCESS => self::STATUS_SUCCESS,
        self::CODE_INVALID_CREDENTIALS => self::STATUS_INVALID_CREDENTIALS,
        self::CODE_AUTHENTICATION_ERROR => self::STATUS_AUTHENTICATION_ERROR,
        self::CODE_UNAUTHORIZED => self::STATUS_UNAUTHORIZED,
        self::CODE_MISSING_HEADER => self::STATUS_MISSING_HEADER,
        self::CODE_INVALID_DATA => self::STATUS_INVALID_DATA,
        self::CODE_MISSING_CURRENT_EXPIRATION_YEAR => self::STATUS_MISSING_CURRENT_EXPIRATION_YEAR,
        self::CODE_INVALID_IP => self::STATUS_INVALID_IP,
    ];

    /**
     * @const string Unknown status.
     */
    const STATUS_UNKNOWN = 'unknown';

    /**
     * @const string The Closing Ops Envelope string.
     */
    const OPS_ENVELOPE = '</OPS_envelope>';

    /** @var int Success */
    const CODE_SUCCESS = 200;

    /** @var int Unknown */
    const CODE_UNKNOWN = 999;

    /** @var string
     * "Data conversion error. Check the command 'modify' syntax"
     * "Domain Already Renewed"
     */
    const STATUS_INVALID_DATA = 'invalid_data';

    const CODE_AUTHENTICATION_ERROR = 415;
    const CODE_INVALID_CREDENTIALS = 400;
    const CODE_INVALID_DATA = 465;
    const CODE_INVALID_IP = 555;
    const CODE_MISSING_CURRENT_EXPIRATION_YEAR = 480;
    const CODE_MISSING_HEADER = 404;
    const CODE_UNAUTHORIZED = 401;

    /** @var string "Authentication Error." */
    const STATUS_AUTHENTICATION_ERROR = 'authentication_error';
    const STATUS_INVALID_CREDENTIALS = 'invalid_credentials';
    const STATUS_INVALID_IP = 'invalid_ip';
    /**
     * @var string
     * "Current expiration year must be specified"
     * @see https://domains.opensrs.guide/docs/renew-domain-
     */
    const STATUS_MISSING_CURRENT_EXPIRATION_YEAR = 'missing_currentexpirationyear';
    const STATUS_MISSING_HEADER = 'missing_header';
    const STATUS_SUCCESS = 'success';
    const STATUS_UNAUTHORIZED = 'unauthorized';

    /**
     * @param string $content
     * @return array
     * @throws UnexpectedValueException
     */
    public static function formatResult(string $content): array
    {
        $xml = self::parseXml($content);
        $dataBlock = [];
        foreach ($xml->body->data_block->dt_assoc->item as $item) {
            $key = (string)$item->attributes()['key'];
            $value = $item;
            $dataBlock[$key] = $value;
        }
        $responseCode = self::CODE_UNKNOWN;
        $status = self::STATUS_UNKNOWN;
        if (isset($dataBlock['response_code'])) {
            $responseCode = (int)$dataBlock['response_code'];
            $responseCodes = self::RESPONSE_CODES;
            if (isset($responseCodes[$responseCode]) === true) {
                $status = $responseCodes[$responseCode];
            }
        }
        $attributes = [];
        if (array_key_exists('attributes', $dataBlock)) {
            foreach ($dataBlock['attributes']->dt_assoc->item as $item) {
                $key = (string)$item->attributes()['key'];
                $value = self::parseItem($item);
                $attributes[$key] = $value;
            }
        }
        $response = isset($dataBlock['response_text']) ? (string)$dataBlock['response_text'] : '';
        return [
            'response' => $response,
            'code' => $responseCode,
            'status' => $status,
            'attributes' => $attributes
        ];
    }

    /**
     * @param string $content
     * @return SimpleXMLElement
     * @throws UnexpectedValueException
     */
    public static function parseXml(string $content): SimpleXMLElement
    {
        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (is_object($xml) === false) {
            throw new UnexpectedValueException('Invalid XML response.');
        }
        return $xml;
    }

    /**
     * @param SimpleXMLElement $item
     * @return array|string
     */
    private static function parseItem(SimpleXMLElement $item)
    {
        if (isset($item->dt_assoc) || isset($item->dt_array)) {
            $value = [];
            $array = empty($item->dt_assoc->item) ? $item->dt_array->item : $item->dt_assoc->item;
            if (empty($array) === false) {
                foreach ($array as $subItem) {
                    $key = (string)$subItem->attributes()['key'];
                    $value[$key] = self::parseItem($subItem);
                }
            }
        } else {
            $value = (string)$item;
        }
        return $value;
    }

    /**
     * @param string $content
     */
    public static function checkContent(string $content): void
    {
        if (empty($content)) {
            throw new DomainException('Empty response');
        }
    }

    /**
     * @param string $contents
     * @param array $responseHeaders
     * @return string
     */
    public static function parseContents(string $contents, array $responseHeaders): string
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