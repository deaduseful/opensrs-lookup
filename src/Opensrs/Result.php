<?php

namespace Deaduseful\Opensrs;

class Result
{
    protected string $response;
    protected int $code;
    protected string $status;
    protected array $attributes;

    public function __construct(string $response, int $code, string $status, array $attributes)
    {
        $this->response = $response;
        $this->code = $code;
        $this->status = $status;
        $this->attributes = $attributes;
    }

    public function toArray(): array
    {
        return [
            'response' => $this->response,
            'code' => $this->code,
            'status' => $this->status,
            'attributes' => $this->attributes,
        ];
    }
}
