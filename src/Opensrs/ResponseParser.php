<?php

namespace Deaduseful\Opensrs;

use DomainException;
use SimpleXMLElement;
use UnexpectedValueException;

class ResponseParser
{
    /**
     * @const string The Closing Ops Envelope string.
     */
    public const OPS_ENVELOPE = '</OPS_envelope>';

    private ?Result $result = null;

    /**
     * @throws UnexpectedValueException
     */
    public function parseResult(string $content): self
    {
        $xml = $this->parseXml($content);
        $dataBlock = [];
        foreach ($xml->body->data_block->dt_assoc->item as $item) {
            $key = (string)$item->attributes()['key'];
            $value = $item;
            $dataBlock[$key] = $value;
        }
        $responseCode = ResponseCode::UNKNOWN;
        $status = 'unknown';
        if (isset($dataBlock['response_code'])) {
            $responseCode = (int)$dataBlock['response_code'];
            $status = ResponseCode::getStatus($responseCode);
        }
        $attributes = [];
        if (array_key_exists('attributes', $dataBlock)) {
            foreach ($dataBlock['attributes']->dt_assoc->item as $item) {
                $key = (string)$item->attributes()['key'];
                $value = $this->parseItem($item);
                $attributes[$key] = $value;
            }
        }
        $response = isset($dataBlock['response_text']) ? (string)$dataBlock['response_text'] : '';
        if (!ResponseCode::isSuccess($responseCode)) {
            throw new DomainException($response, $responseCode);
        }
        $this->result = new Result($response, $responseCode, $status, $attributes);
        return $this;
    }

    /**
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

    private function parseItem(SimpleXMLElement $item)
    {
        if (isset($item->dt_assoc) || isset($item->dt_array)) {
            $value = [];
            $dtAssocItems = $item->dt_assoc->item ?? null;
            $dtArrayItems = $item->dt_array->item ?? null;
            $array = ($dtAssocItems === null || count($dtAssocItems) === 0) ? $dtArrayItems : $dtAssocItems;
            if ($array !== null && count($array) > 0) {
                foreach ($array as $subItem) {
                    $key = (string)$subItem->attributes()['key'];
                    $value[$key] = $this->parseItem($subItem);
                }
            }
        } else {
            $value = (string)$item;
        }
        return $value;
    }

    public function checkContent(string $content): self
    {
        if (empty($content)) {
            throw new DomainException('Empty response');
        }
        return $this;
    }

    public function parseContents(string $contents, array $responseHeaders): string
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

    public function getResult(): ?Result
    {
        return $this->result;
    }
}