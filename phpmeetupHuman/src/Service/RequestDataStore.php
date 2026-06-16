<?php

namespace App\Service;

class RequestDataStore
{
    /**
     * @var array<string, mixed>
     */
    private array $storage = [];

    public function set(string $key, mixed $data): void
    {
        $this->storage[$key] = $data;
    }

    public function get(string $key): mixed
    {
        return $this->storage[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->storage);
    }
}
