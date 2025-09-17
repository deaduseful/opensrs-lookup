<?php

namespace Deaduseful\Opensrs;

use RuntimeException;

class RequestClient
{
    /** @const int Socket Timeout in seconds. */
    public const SOCKET_TIMEOUT = 120;
    protected string $contents;
    protected array $headers;

    public function call(string $request, string $headers, string $host, int $timeout = self::SOCKET_TIMEOUT): self
    {
        $contents = $this->filePostContents($host, $request, $headers, $timeout);
        if (empty($contents)) {
            throw new RuntimeException('Empty response');
        }
        $this->contents = $contents;
        $this->headers = $this->getResponseHeaders();
        return $this;
    }

    protected function getResponseHeaders(): array
    {
        return $http_response_header ?? [];
    }

    /**
     * Similar to file_get_contents but uses the POST method.
     */
    public function filePostContents(string $url, string $content, string $headers = '', int $timeout = self::SOCKET_TIMEOUT)
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
        return $this->fileGetContents($url, $options);
    }

    protected function fileGetContents(string $filename, array $options = []) {
        $context = stream_context_create($options);
        return file_get_contents($filename, false, $context);
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
