<?php

namespace Deaduseful\Opensrs;

include __DIR__ . '/../bootstrap.php';

class MockRequestClient extends RequestClient
{
    private array $mockResponses = [];
    private string $currentResponse = '';

    public function setMockResponse(string $key, string $response): self
    {
        $this->mockResponses[$key] = $response;
        return $this;
    }

    public function call(string $request, string $headers, string $host, int $timeout = self::SOCKET_TIMEOUT): self
    {
        // Determine which mock response to use based on the request content
        $this->currentResponse = $this->determineMockResponse($request);
        $this->contents = $this->currentResponse;
        $this->headers = [];
        return $this;
    }

    private function determineMockResponse(string $request): string
    {
        // Parse the request to determine which test data to return (case-insensitive)
        $requestLower = strtolower($request);
        
        if (strpos($requestLower, 'lookup') !== false && strpos($requestLower, 'example.com') !== false) {
            return $this->mockResponses['lookup'] ?? '';
        }
        
        if (strpos($requestLower, 'check_transfer') !== false) {
            return $this->mockResponses['check_transfer'] ?? '';
        }
        
        if (strpos($requestLower, 'name_suggest') !== false) {
            return $this->mockResponses['suggest'] ?? '';
        }
        
        if (strpos($requestLower, 'get') !== false && strpos($requestLower, 'phurix.com') !== false) {
            if (strpos($requestLower, 'status') !== false) {
                return $this->mockResponses['get_domain_status'] ?? '';
            }
            return $this->mockResponses['get_domain'] ?? '';
        }
        
        if (strpos($requestLower, 'get_domains_by_expiredate') !== false) {
            return $this->mockResponses['get_domains_by_expiredate'] ?? '';
        }
        
        // Default fallback
        return $this->mockResponses['default'] ?? '';
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
