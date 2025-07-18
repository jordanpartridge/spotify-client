<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Contracts;

interface TokenStorageInterface
{
    public function store(string $key, array $token): void;

    public function retrieve(string $key): ?array;

    public function remove(string $key): void;

    public function exists(string $key): bool;
}
