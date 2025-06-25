<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Services;

use Jordanpartridge\SpotifyClient\Auth\SpotifyAuthConnector;
use Jordanpartridge\SpotifyClient\Auth\Requests\ClientCredentialsTokenRequest;
use Saloon\Exceptions\Request\RequestException;

class SpotifyAppManager
{
    private const DEVELOPER_DASHBOARD_URL = 'https://developer.spotify.com/dashboard/applications';
    private const CREATE_APP_URL = 'https://developer.spotify.com/dashboard/create';

    public function __construct(
        private readonly SpotifyAuthConnector $authConnector
    ) {}

    public function detectExistingApps(): array
    {
        // Note: Spotify doesn't provide a public API to list user's apps
        // This would require web scraping or user providing app details
        // For now, we'll simulate this or ask user to provide manually
        
        // In a real implementation, you might:
        // 1. Use Spotify's undocumented endpoints (risky)
        // 2. Ask user to export app list
        // 3. Store previously created apps locally
        
        return $this->getLocallyStoredApps();
    }

    public function openDeveloperDashboard(): void
    {
        $this->openUrl(self::DEVELOPER_DASHBOARD_URL);
    }

    public function openCreateAppPage(): void
    {
        $this->openUrl(self::CREATE_APP_URL);
    }

    public function generateOptimalAppSettings(string $appName, string $description, string $appUrl): array
    {
        return [
            'name' => $appName,
            'description' => $description,
            'website' => $appUrl,
            'redirect_uris' => $this->generateRedirectUris($appUrl),
            'bundle_ids' => [],
            'android_packages' => [],
        ];
    }

    public function validateAppConfiguration(array $config): array
    {
        $errors = [];

        if (empty($config['name'])) {
            $errors[] = 'App name is required';
        }

        if (strlen($config['name']) > 32) {
            $errors[] = 'App name must be 32 characters or less';
        }

        if (empty($config['description'])) {
            $errors[] = 'App description is required';
        }

        if (strlen($config['description']) > 512) {
            $errors[] = 'App description must be 512 characters or less';
        }

        return $errors;
    }

    public function storeAppLocally(array $appConfig): void
    {
        $appsFile = $this->getAppsStoragePath();
        $apps = $this->getLocallyStoredApps();

        $apps[] = [
            'id' => $appConfig['client_id'],
            'name' => $appConfig['name'],
            'description' => $appConfig['description'] ?? '',
            'created_at' => now()->toDateTimeString(),
            'last_used' => now()->toDateTimeString(),
        ];

        file_put_contents($appsFile, json_encode($apps, JSON_PRETTY_PRINT));
    }

    public function updateAppLastUsed(string $clientId): void
    {
        $appsFile = $this->getAppsStoragePath();
        $apps = $this->getLocallyStoredApps();

        foreach ($apps as &$app) {
            if ($app['id'] === $clientId) {
                $app['last_used'] = now()->toDateTimeString();
                break;
            }
        }

        file_put_contents($appsFile, json_encode($apps, JSON_PRETTY_PRINT));
    }

    public function testAppCredentials(string $clientId, string $clientSecret): array
    {
        try {
            $request = new ClientCredentialsTokenRequest($clientId, $clientSecret);
            $response = $this->authConnector->send($request);
            $data = $response->json();

            return [
                'valid' => true,
                'access_token' => $data['access_token'] ?? null,
                'token_type' => $data['token_type'] ?? null,
                'expires_in' => $data['expires_in'] ?? null,
            ];

        } catch (RequestException $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
                'suggestion' => $this->getCredentialErrorSuggestion($e),
            ];
        }
    }

    public function generateAppInstructions(): array
    {
        return [
            'step1' => [
                'title' => 'Navigate to Spotify Developer Dashboard',
                'description' => 'Go to https://developer.spotify.com/dashboard/applications',
                'action' => 'Click "Create an App"',
            ],
            'step2' => [
                'title' => 'Fill in App Details',
                'description' => 'Enter the app name and description I suggested',
                'notes' => [
                    'App name should be descriptive but under 32 characters',
                    'Description should explain what your app does',
                    'Website URL should be your Laravel app URL',
                ],
            ],
            'step3' => [
                'title' => 'Configure Settings',
                'description' => 'Set up redirect URIs and other settings',
                'notes' => [
                    'Add redirect URIs for OAuth callback',
                    'Leave bundle IDs and Android packages empty unless needed',
                    'Accept Spotify Developer Terms of Service',
                ],
            ],
            'step4' => [
                'title' => 'Get Credentials',
                'description' => 'Copy your Client ID and Client Secret',
                'notes' => [
                    'Client ID is publicly visible',
                    'Client Secret should be kept private',
                    'You can regenerate the Client Secret if needed',
                ],
            ],
        ];
    }

    public function checkSpotifyApiStatus(): array
    {
        try {
            $response = $this->httpClient->get('https://api.spotify.com/', [
                'timeout' => 5,
            ]);

            return [
                'available' => $response->getStatusCode() === 200,
                'response_time' => null, // Could add timing
            ];

        } catch (RequestException $e) {
            return [
                'available' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function openUrl(string $url): void
    {
        $command = match (PHP_OS_FAMILY) {
            'Darwin' => "open '{$url}'",
            'Linux' => "xdg-open '{$url}'",
            'Windows' => "start '{$url}'",
            default => null,
        };

        if ($command) {
            exec($command);
        }
    }

    private function generateRedirectUris(string $appUrl): array
    {
        $baseUrl = rtrim($appUrl, '/');
        
        return [
            "{$baseUrl}/auth/spotify/callback",
            "{$baseUrl}/spotify/callback",
            'http://localhost:8080/callback', // For local OAuth server
            'http://localhost:3000/callback', // Common dev port
        ];
    }

    private function getAppsStoragePath(): string
    {
        $storageDir = storage_path('spotify-client');
        
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        return $storageDir . '/apps.json';
    }

    private function getLocallyStoredApps(): array
    {
        $appsFile = $this->getAppsStoragePath();
        
        if (!file_exists($appsFile)) {
            return [];
        }

        $content = file_get_contents($appsFile);
        $apps = json_decode($content, true);

        return is_array($apps) ? $apps : [];
    }

    private function getCredentialErrorSuggestion(\Exception $e): string
    {
        $message = $e->getMessage();

        return match (true) {
            str_contains($message, '401') => 'Invalid Client ID or Client Secret. Please check your credentials.',
            str_contains($message, '400') => 'Malformed request. Ensure your credentials are properly formatted.',
            str_contains($message, '429') => 'Rate limited. Please wait a moment and try again.',
            str_contains($message, 'curl') => 'Network error. Check your internet connection.',
            default => 'Unexpected error. Please verify your credentials and try again.',
        };
    }
}