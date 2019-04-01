<?php

namespace Deaduseful\Opensrs;

use Exception;

class FastLookup
{
    /**
     * OpenSRS domain service API url.
     * LIVE => rr-n1-tor.opensrs.net, TEST => horizon.opensrs.net
     */
    const OSRS_HOST = 'rr-n1-tor.opensrs.net';

    /**
     * OpenSRS API fast lookup port.
     */
    const OSRS_FASTLOOKUP_PORT = 51000;

    /**
     * @var array
     */
    private $result = [];

    /**
     * @var string
     */
    private $host = self::OSRS_HOST;

    /**
     * @var string
     */
    private $port = self::OSRS_FASTLOOKUP_PORT;

    /**
     * FastLookup constructor.
     * @param string $query The domain to query.
     */
    function __construct($query)
    {
        $this->checkDomain($query);
    }

    /**
     * @param string $query
     * @return void
     * @throws Exception
     */
    public function checkDomain($query)
    {
        $command = "check_domain $query" . PHP_EOL;
        $response = self::lookup($command, $this->getHost(), $this->getPort());
        $results = explode(' ', $response, 2);
        $responseCode = trim($results[0]);
        if (empty($responseCode)) {
            throw new Exception('Empty response.');
        }
        $responseCodes = [
            210 => 'available',
            211 => 'taken',
            465 => 'invalid_domain',
            5050 => 'invalid_command',
            555 => 'invalid_ip',
            701 => 'unknown_tld',
        ];
        if (array_key_exists($responseCode, $responseCodes) === false) {
            throw new Exception('Unexpected response: ' . $response);
        }
        $result = [
            'response' => $response,
            'code' => $responseCode,
            'status' => $responseCodes[$responseCode]
        ];
        $this->setResult($result);
    }

    /**
     * @param string $payload
     * @param string $host
     * @param int $port
     * @param int $length
     * @param int $timeout
     * @return string
     */
    public function lookup($payload, $host = self::OSRS_HOST, $port = self::OSRS_FASTLOOKUP_PORT, $length = 2048, $timeout = 1)
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
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @param string $port
     * @return FastLookup
     */
    public function setPort(string $port): FastLookup
    {
        $this->port = $port;
        return $this;
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
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}
