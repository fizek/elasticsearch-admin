<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class CallRequestModel extends AbstractAppModel
{
    private ?string $method = null;

    private ?string $path = null;

    private ?array $options = null;

    public function __construct()
    {
        $this->method = 'GET';
        $this->options = ['query' => [], 'json' => [], 'body' => null];
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        if ('/' != substr($path, 0, 1)) {
            $path = '/'.$path;
        }
        $this->path = $path;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getQuery(): ?array
    {
        return $this->options['query'] ?? null;
    }

    public function setQuery(?array $query): self
    {
        $this->options['query'] = $query;

        return $this;
    }

    public function getJson(): ?array
    {
        return $this->options['json'] ?? null;
    }

    public function setJson(?array $json): self
    {
        $this->options['json'] = $json;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->options['body'] ?? null;
    }

    public function setBody(?string $body): self
    {
        $this->options['body'] = $body;

        return $this;
    }
}
