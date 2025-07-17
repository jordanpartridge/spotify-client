<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Auth\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasFormBody;

class AuthorizationCodeTokenRequest extends Request
{
    use HasFormBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $code,
        private readonly string $redirectUri,
        private readonly string $clientId,
        private readonly string $clientSecret
    ) {}

    public function resolveEndpoint(): string
    {
        return '/api/token';
    }

    protected function defaultBody(): array
    {
        return [
            'grant_type' => 'authorization_code',
            'code' => $this->code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
    }
}