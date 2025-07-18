<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Services;

use Jordanpartridge\SpotifyClient\Auth\Requests\AuthorizationCodeTokenRequest;
use Jordanpartridge\SpotifyClient\Auth\Requests\ClientCredentialsTokenRequest;
use Jordanpartridge\SpotifyClient\Auth\Requests\RefreshTokenRequest;
use Jordanpartridge\SpotifyClient\Auth\SpotifyAuthConnector;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;
use Saloon\Exceptions\Request\RequestException;

class OAuthFlowHandler
{
    private const SPOTIFY_AUTH_URL = 'https://accounts.spotify.com/authorize';

    private const SPOTIFY_TOKEN_URL = 'https://accounts.spotify.com/api/token';

    private ?string $authorizationCode = null;

    private ?string $state = null;

    private ?array $tokens = null;

    private ?string $redirectUri = null;

    public function __construct(
        private readonly SpotifyAuthConnector $authConnector
    ) {}

    public function getClientCredentialsToken(string $clientId, string $clientSecret): array
    {
        try {
            $request = new ClientCredentialsTokenRequest($clientId, $clientSecret);
            $response = $this->authConnector->send($request);
            $data = $response->json();

            return [
                'access_token' => $data['access_token'],
                'token_type' => $data['token_type'],
                'expires_in' => $data['expires_in'],
                'expires_at' => time() + $data['expires_in'],
            ];

        } catch (RequestException $e) {
            throw new \Exception("Failed to get access token: {$e->getMessage()}");
        }
    }

    public function generateAuthorizationUrl(string $clientId, string $redirectUri, array $scopes): string
    {
        $this->state = bin2hex(random_bytes(16));
        $this->redirectUri = $redirectUri; // Store for later use

        $params = [
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => $this->state,
            'show_dialog' => 'true', // Force user to see auth dialog
        ];

        return self::SPOTIFY_AUTH_URL.'?'.http_build_query($params);
    }

    public function generateAuthorizationUrlWithPKCE(string $clientId, string $redirectUri, array $scopes): array
    {
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);
        $this->state = bin2hex(random_bytes(16));
        $this->redirectUri = $redirectUri; // Store for later use

        $params = [
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => $this->state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
            'show_dialog' => 'true',
        ];

        return [
            'url' => self::SPOTIFY_AUTH_URL.'?'.http_build_query($params),
            'code_verifier' => $codeVerifier,
            'state' => $this->state,
        ];
    }

    public function startCallbackServer(int $port = 8080, string $host = '127.0.0.1'): string
    {
        $loop = Loop::get();

        $server = new HttpServer(function (ServerRequestInterface $request) {
            return $this->handleCallback($request);
        });

        $socket = new SocketServer("{$host}:{$port}", [], $loop);
        $server->listen($socket);

        // Start the event loop in a non-blocking way
        $loop->futureTick(function () use ($loop) {
            $loop->run();
        });

        // Store the redirect URI for later use and ensure consistent format
        $this->redirectUri = "http://{$host}:{$port}/callback";

        return $this->redirectUri;
    }

    public function waitForCallback(array $appConfig, int $timeoutSeconds = 120): array
    {
        $startTime = time();

        while (time() - $startTime < $timeoutSeconds) {
            if ($this->authorizationCode) {
                return $this->exchangeCodeForTokens($appConfig);
            }

            usleep(100000); // Sleep for 100ms
        }

        throw new \Exception('Timeout waiting for authorization callback');
    }

    public function exchangeCodeForTokens(array $appConfig): array
    {
        if (! $this->authorizationCode) {
            throw new \Exception('No authorization code available');
        }

        if (! $this->redirectUri) {
            throw new \Exception('No redirect URI available. Make sure to call startCallbackServer() first.');
        }

        try {
            $request = new AuthorizationCodeTokenRequest(
                $this->authorizationCode,
                $this->redirectUri,
                $appConfig['client_id'],
                $appConfig['client_secret']
            );

            $response = $this->authConnector->send($request);
            $data = $response->json();

            $this->tokens = [
                'access_token' => $data['access_token'],
                'token_type' => $data['token_type'],
                'expires_in' => $data['expires_in'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'scope' => $data['scope'] ?? null,
                'expires_at' => time() + $data['expires_in'],
            ];

            return $this->tokens;

        } catch (RequestException $e) {
            throw new \Exception("Failed to exchange code for tokens: {$e->getMessage()}");
        }
    }

    public function refreshToken(string $refreshToken, array $appConfig): array
    {
        try {
            $request = new RefreshTokenRequest(
                $refreshToken,
                $appConfig['client_id'],
                $appConfig['client_secret']
            );

            $response = $this->authConnector->send($request);
            $data = $response->json();

            return [
                'access_token' => $data['access_token'],
                'token_type' => $data['token_type'],
                'expires_in' => $data['expires_in'],
                'refresh_token' => $data['refresh_token'] ?? $refreshToken,
                'scope' => $data['scope'] ?? null,
                'expires_at' => time() + $data['expires_in'],
            ];

        } catch (RequestException $e) {
            throw new \Exception("Failed to refresh token: {$e->getMessage()}");
        }
    }

    public function openBrowser(string $url): void
    {
        $command = match (PHP_OS_FAMILY) {
            'Darwin' => "open '{$url}'",
            'Linux' => "xdg-open '{$url}'",
            'Windows' => "start '{$url}'",
            default => null,
        };

        if ($command) {
            exec($command.' > /dev/null 2>&1 &');
        }
    }

    public function validateState(string $receivedState): bool
    {
        return $this->state && hash_equals($this->state, $receivedState);
    }

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    private function handleCallback(ServerRequestInterface $request): Response
    {
        $query = $request->getQueryParams();

        // Handle authorization response
        if (isset($query['code']) && isset($query['state'])) {
            if (! $this->validateState($query['state'])) {
                return new Response(400, [], $this->generateErrorPage('Invalid state parameter'));
            }

            $this->authorizationCode = $query['code'];

            return new Response(200, [], $this->generateSuccessPage());
        }

        // Handle error response
        if (isset($query['error'])) {
            $error = $query['error'];
            $description = $query['error_description'] ?? 'Unknown error';

            return new Response(400, [], $this->generateErrorPage("Authorization failed: {$error} - {$description}"));
        }

        // Default callback page
        return new Response(200, [], $this->generateCallbackPage());
    }

    private function generateCallbackPage(): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Spotify Authorization</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; text-align: center; padding: 50px; }
                .container { max-width: 500px; margin: 0 auto; }
                .loading { color: #1db954; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🎵 Spotify Authorization</h1>
                <p class="loading">Waiting for authorization...</p>
                <p>This page will automatically update when authorization is complete.</p>
            </div>
        </body>
        </html>';
    }

    private function generateSuccessPage(): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Authorization Successful</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; text-align: center; padding: 50px; }
                .container { max-width: 500px; margin: 0 auto; }
                .success { color: #1db954; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🎉 Authorization Successful!</h1>
                <p class="success">You can now close this window and return to your terminal.</p>
                <p>Your Spotify integration is being set up...</p>
            </div>
            <script>
                setTimeout(() => {
                    window.close();
                }, 3000);
            </script>
        </body>
        </html>';
    }

    private function generateErrorPage(string $error): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Authorization Error</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; text-align: center; padding: 50px; }
                .container { max-width: 500px; margin: 0 auto; }
                .error { color: #e74c3c; }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <h1>❌ Authorization Failed</h1>
                <p class=\"error\">{$error}</p>
                <p>Please close this window and try again in your terminal.</p>
            </div>
        </body>
        </html>";
    }

    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function generateCodeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }
}
