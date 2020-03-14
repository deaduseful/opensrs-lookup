<?php

namespace Deaduseful\Opensrs;

use DomainException;
use Exception;

class FastLookup
{
    /**
     * OpenSRS domain service API url.
     * LIVE => rr-n1-tor.opensrs.net, TEST => horizon.opensrs.net
     */
    const HOST = 'rr-n1-tor.opensrs.net';

    /**
     * OpenSRS API fast lookup port.
     */
    const PORT = 51000;

    /**
     * Success response codes and their status.
     */
    const SUCCESS_RESPONSE_CODES = [
        210 => 'available',
        211 => 'taken',
    ];

    /**
     * Failure response codes and their status.
     */
    const FAILURE_RESPONSE_CODES = [
        465 => 'invalid_domain',
        5050 => 'invalid_command',
        555 => 'invalid_ip',
        701 => 'unknown_tld',
    ];

    /**
     * Unknown status.
     */
    const STATUS_UNKNOWN = 'unknown';

    /**
     * @var array
     */
    private $result = [];

    /**
     * @var string
     */
    private $host = self::HOST;

    /**
     * @var int
     */
    private $port = self::PORT;

    /**
     * @param string $query
     * @return bool|null
     * @throws Exception
     */
    function available(string $query)
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

    /**
     * Lookup.
     *
     * @param string $query The domain to query.
     * @return array
     * @throws Exception
     */
    public function lookup(string $query)
    {
        return $this->checkDomain($query)->getResult();
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
     * @return FastLookup
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @param string $query
     * @return FastLookup
     * @throws DomainException
     */
    public function checkDomain(string $query)
    {
        $command = "check_domain $query" . PHP_EOL;
        $response = trim(self::query($command, $this->getHost(), $this->getPort()));
        $results = explode(' ', $response, 2);
        $responseCode = (int)trim($results[0]);
        if (empty($responseCode)) {
            throw new DomainException('Empty response code.');
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
        $result = [
            'success' => $success,
            'response' => $response,
            'code' => $responseCode,
            'status' => $status,
        ];
        return $this->setResult($result);
    }

    /**
     * @param string $payload
     * @param string $host
     * @param int $port
     * @param int $length
     * @param int $timeout
     * @return string
     */
    public function query(string $payload, string $host = self::HOST, int $port = self::PORT, int $timeout = 1, int $length = 2048)
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

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return FastLookup
     */
    public function setHost(string $host): FastLookup
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return FastLookup
     */
    public function setPort(int $port): FastLookup
    {
        $this->port = $port;
        return $this;
    }
}
