<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Auth;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class SpotifyAuthConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return 'https://accounts.spotify.com';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ];
    }
}