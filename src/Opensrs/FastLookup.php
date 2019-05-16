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
     * @var integer
     */
    private $port = self::OSRS_FASTLOOKUP_PORT;

    /**
     * Lookup.
     *
     * @param string $query The domain to query.
     * @return array
     * @throws Exception
     */
    public function lookup($query)
    {
        $this->checkDomain($query);
        return $this->getResult();
    }

    /**
     * @param string $query
     * @return void
     * @throws Exception
     */
    public function checkDomain($query)
    {
        $command = "check_domain $query" . PHP_EOL;
        $response = self::query($command, $this->getHost(), $this->getPort());
        $results = explode(' ', $response, 2);
        $responseCode = (int)trim($results[0]);
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
     * @param integer $port
     * @param integer $length
     * @param integer $timeout
     * @return string
     */
    public function query($payload, $host = self::OSRS_HOST, $port = self::OSRS_FASTLOOKUP_PORT, $length = 2048, $timeout = 1)
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
