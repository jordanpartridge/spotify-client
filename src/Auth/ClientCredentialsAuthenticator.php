<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Auth;

use Illuminate\Support\Carbon;
use Jordanpartridge\SpotifyClient\Auth\Requests\ClientCredentialsTokenRequest;
use Jordanpartridge\SpotifyClient\Contracts\AuthenticatorInterface;
use Jordanpartridge\SpotifyClient\Contracts\TokenStorageInterface;

class ClientCredentialsAuthenticator implements AuthenticatorInterface
{
    private const TOKEN_KEY = 'client_credentials_token';

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly TokenStorageInterface $tokenStorage
    ) {}

    public function getAccessToken(): string
    {
        $token = $this->tokenStorage->retrieve(self::TOKEN_KEY);

        if (! $token || $this->isTokenExpired($token)) {
            $token = $this->requestNewToken();
            $this->tokenStorage->store(self::TOKEN_KEY, $token);
        }

        return $token['access_token'];
    }

    public function isExpired(): bool
    {
        $token = $this->tokenStorage->retrieve(self::TOKEN_KEY);

        return ! $token || $this->isTokenExpired($token);
    }

    public function refresh(): void
    {
        $token = $this->requestNewToken();
        $this->tokenStorage->store(self::TOKEN_KEY, $token);
    }

    private function requestNewToken(): array
    {
        $authConnector = new SpotifyAuthConnector;
        $request = new ClientCredentialsTokenRequest($this->clientId, $this->clientSecret);

        $response = $authConnector->send($request);
        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'token_type' => $data['token_type'],
            'expires_in' => $data['expires_in'],
            'expires_at' => Carbon::now()->addSeconds($data['expires_in'])->timestamp,
        ];
    }

    private function isTokenExpired(array $token): bool
    {
        return Carbon::now()->timestamp >= $token['expires_at'];
    }
}
