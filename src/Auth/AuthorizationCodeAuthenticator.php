<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Auth;

use Illuminate\Support\Carbon;
use Jordanpartridge\SpotifyClient\Auth\Requests\AuthorizationCodeTokenRequest;
use Jordanpartridge\SpotifyClient\Auth\Requests\RefreshTokenRequest;
use Jordanpartridge\SpotifyClient\Contracts\AuthenticatorInterface;
use Jordanpartridge\SpotifyClient\Contracts\TokenStorageInterface;

class AuthorizationCodeAuthenticator implements AuthenticatorInterface
{
    private const TOKEN_KEY = 'authorization_code_token';

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
        private readonly TokenStorageInterface $tokenStorage
    ) {}

    public function getAccessToken(): string
    {
        $token = $this->tokenStorage->retrieve(self::TOKEN_KEY);

        if (! $token) {
            throw new \RuntimeException('No authorization code token found. Please complete OAuth flow first.');
        }

        if ($this->isTokenExpired($token)) {
            $token = $this->refreshToken($token);
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
        $token = $this->tokenStorage->retrieve(self::TOKEN_KEY);
        if (! $token) {
            throw new \RuntimeException('No token to refresh');
        }

        $refreshedToken = $this->refreshToken($token);
        $this->tokenStorage->store(self::TOKEN_KEY, $refreshedToken);
    }

    public function exchangeCodeForToken(string $code): array
    {
        $authConnector = new SpotifyAuthConnector;
        $request = new AuthorizationCodeTokenRequest(
            $this->clientId,
            $this->clientSecret,
            $code,
            $this->redirectUri
        );

        $response = $authConnector->send($request);
        $data = $response->json();

        $token = [
            'access_token' => $data['access_token'],
            'token_type' => $data['token_type'],
            'expires_in' => $data['expires_in'],
            'expires_at' => Carbon::now()->addSeconds($data['expires_in'])->timestamp,
            'refresh_token' => $data['refresh_token'],
            'scope' => $data['scope'] ?? '',
        ];

        $this->tokenStorage->store(self::TOKEN_KEY, $token);

        return $token;
    }

    private function refreshToken(array $token): array
    {
        if (! isset($token['refresh_token'])) {
            throw new \RuntimeException('No refresh token available');
        }

        $authConnector = new SpotifyAuthConnector;
        $request = new RefreshTokenRequest(
            $this->clientId,
            $this->clientSecret,
            $token['refresh_token']
        );

        $response = $authConnector->send($request);
        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'token_type' => $data['token_type'],
            'expires_in' => $data['expires_in'],
            'expires_at' => Carbon::now()->addSeconds($data['expires_in'])->timestamp,
            'refresh_token' => $data['refresh_token'] ?? $token['refresh_token'],
            'scope' => $data['scope'] ?? $token['scope'],
        ];
    }

    private function isTokenExpired(array $token): bool
    {
        return Carbon::now()->timestamp >= $token['expires_at'];
    }
}
