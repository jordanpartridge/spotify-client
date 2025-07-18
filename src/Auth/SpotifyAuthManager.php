<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Auth;

use Illuminate\Support\Manager;
use Jordanpartridge\SpotifyClient\Contracts\AuthenticatorInterface;
use Jordanpartridge\SpotifyClient\Contracts\TokenStorageInterface;

class SpotifyAuthManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('spotify-client.auth.default_flow', 'client_credentials');
    }

    public function createClientCredentialsDriver(): AuthenticatorInterface
    {
        return new ClientCredentialsAuthenticator(
            $this->config->get('spotify-client.client_id'),
            $this->config->get('spotify-client.client_secret'),
            $this->container->make(TokenStorageInterface::class)
        );
    }

    public function createAuthorizationCodeDriver(): AuthenticatorInterface
    {
        return new AuthorizationCodeAuthenticator(
            $this->config->get('spotify-client.client_id'),
            $this->config->get('spotify-client.client_secret'),
            $this->config->get('spotify-client.auth.redirect_uri'),
            $this->container->make(TokenStorageInterface::class)
        );
    }
}
