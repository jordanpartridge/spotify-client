<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Contracts;

interface AuthenticatorInterface
{
    public function getAccessToken(): string;

    public function isExpired(): bool;

    public function refresh(): void;
}