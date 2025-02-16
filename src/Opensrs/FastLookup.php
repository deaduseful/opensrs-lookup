<?php

namespace Deaduseful\Opensrs;

use DomainException;

class FastLookup
{
    /**
     * @const string LIVE OpenSRS domain service API host.
     */
    protected const LIVE_HOST = 'rr-n1-tor.opensrs.net';

    /**
     * @const string TEST OpenSRS domain service API host.
     */
    protected const TEST_HOST = 'horizon.opensrs.net';

    /**
     * @const int OpenSRS API fast lookup port.
     */
    protected const PORT = 51000;

    /**
     * @const int Default Timeout.
     */
    public const TIMEOUT = 1;

    /**
     * @const int Default Length.
     */
    public const LENGTH = 2048;

    /**
     * @const string[] Success response codes and their status.
     */
    public const SUCCESS_RESPONSE_CODES = [
        210 => self::STATUS_AVAILABLE,
        211 => self::STATUS_TAKEN,
    ];

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_TAKEN = 'taken';

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
    public const COMMAND_CHECK_DOMAIN = 'check_domain';
    protected array $result = [];
    protected string $host = self::LIVE_HOST;
    protected int $port = self::PORT;
    protected int $timeout = self::TIMEOUT;
    protected int $length = self::LENGTH;
    protected ?string $response = null;

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

    /**
     * @throws DomainException
     */
    public function checkDomain(string $query): self
    {
        $command = self::COMMAND_CHECK_DOMAIN . ' ' . $query . PHP_EOL;
        return $this->query($command)->formatResponse();
    }

    protected function query(string $payload): self
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $options = ['sec' => $this->timeout, 'usec' => 0];
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $options);
        socket_connect($socket, $this->host, $this->port);
        socket_send($socket, $payload, strlen($payload), 0);
        $out = socket_read($socket, $this->length);
        socket_close($socket);
        $this->response = $out;
        return $this;
    }

    /**
     * @throws DomainException
     */
    protected function formatResponse(): self
    {
        $response = $this->response;
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
        $this->result = [
            'success' => $success,
            'response' => $response,
            'code' => $responseCode,
            'status' => $status,
        ];
        return $this;
    }

    public function setHost(bool $test): FastLookup
    {
        $this->host = $test ? self::TEST_HOST : self::LIVE_HOST;
        return $this;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function setPort(int $port): FastLookup
    {
        $this->port = $port;
        return $this;
    }

    public function setLength(int $length): FastLookup
    {
        $this->length = $length;
        return $this;
    }
}
