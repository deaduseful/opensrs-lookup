<?php

namespace Deaduseful\Opensrs;

use DomainException;

class FastLookup
{
    /**
     * @const string LIVE OpenSRS domain service API host.
     */
    public const LIVE_HOST = 'rr-n1-tor.opensrs.net';

    /**
     * @const string TEST OpenSRS domain service API host.
     */
    public const TEST_HOST = 'horizon.opensrs.net';

    /**
     * @const int OpenSRS API fast lookup port.
     */
    public const PORT = 51000;

    /**
     * @const string[] Success response codes and their status.
     */
    public const SUCCESS_RESPONSE_CODES = [
        210 => 'available',
        211 => 'taken',
    ];

    /**
     * * @const string[] Failure response codes and their status.
     */
    public const FAILURE_RESPONSE_CODES = [
        465 => 'invalid_domain',
        5050 => 'invalid_command',
        555 => 'invalid_ip',
        701 => 'unknown_tld',
    ];
    public const STATUS_UNKNOWN = 'unknown';
    private $result = [];
    private $host = self::LIVE_HOST;
    private $port = self::PORT;

    public function available(string $query): ?bool
    {
        $result = $this->lookup($query);
        if ($result['status'] === 'taken') {
            return false;
        }
        if ($result['status'] === 'available') {
            return true;
        }
        return null;
    }

    public function lookup(string $query): array
    {
        return $this->checkDomain($query)->getResult();
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function setResult(array $result): FastLookup
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @throws DomainException
     */
    public function checkDomain(string $query): FastLookup
    {
        $command = "check_domain $query" . PHP_EOL;
        $response = $this->query($command, $this->getHost(), $this->getPort());
        $result = $this->formatResponse($response);
        return $this->setResult($result);
    }

    public static function query(string $payload, string $host = self::LIVE_HOST, int $port = self::PORT, int $timeout = 1, int $length = 2048): string
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $options = ['sec' => $timeout, 'usec' => 0];
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $options);
        socket_connect($socket, $host, $port);
        socket_send($socket, $payload, strlen($payload), 0);
        $out = socket_read($socket, $length);
        socket_close($socket);
        return $out;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): FastLookup
    {
        $this->host = $host;
        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): FastLookup
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @throws DomainException
     */
    public static function formatResponse(string $response): array
    {
        $results = explode(' ', trim($response), 2);
        $responseCode = (int)trim($results[0]);
        if (empty($responseCode)) {
            throw new DomainException('Empty Response Code');
        }
        if (array_key_exists($responseCode, self::SUCCESS_RESPONSE_CODES) === true) {
            $status = self::SUCCESS_RESPONSE_CODES[$responseCode];
            $success = true;
        } elseif (array_key_exists($responseCode, self::FAILURE_RESPONSE_CODES) === true) {
            $status = self::FAILURE_RESPONSE_CODES[$responseCode];
            $success = false;
        } else {
            $status = self::STATUS_UNKNOWN;
            $success = false;
        }
        return [
            'success' => $success,
            'response' => $response,
            'code' => $responseCode,
            'status' => $status,
        ];
    }
}
