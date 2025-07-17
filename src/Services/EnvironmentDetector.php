<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Services;

use Illuminate\Support\Facades\File;

class EnvironmentDetector
{
    private array $analysis = [];

    public function analyzeEnvironment(): array
    {
        $this->analysis = [
            'laravel_version' => $this->detectLaravelVersion(),
            'app_type' => $this->detectAppType(),
            'packages' => $this->detectInstalledPackages(),
            'existing_spotify_config' => $this->hasExistingSpotifyConfig(),
            'env_structure' => $this->analyzeEnvStructure(),
            'app_name' => $this->getAppName(),
            'app_url' => $this->getAppUrl(),
            'environment' => $this->getEnvironment(),
        ];

        return $this->analysis;
    }

    public function detectLaravelVersion(): ?string
    {
        if (! function_exists('app')) {
            return null;
        }

        return app()->version();
    }

    public function detectAppType(): string
    {
        // Check if it's API-only
        if ($this->isApiOnly()) {
            return 'api-only';
        }

        // Check for SPA indicators
        if ($this->isSpa()) {
            return 'spa';
        }

        // Check for traditional web app
        if ($this->isWebApp()) {
            return 'web-app';
        }

        return 'unknown';
    }

    public function detectInstalledPackages(): array
    {
        $packages = [];
        $composerLock = $this->getComposerLockContent();

        if (! $composerLock) {
            return $packages;
        }

        $packageNames = [
            'livewire/livewire' => 'Livewire',
            'inertiajs/inertia-laravel' => 'Inertia.js',
            'filament/filament' => 'Filament',
            'laravel/sanctum' => 'Sanctum',
            'laravel/passport' => 'Passport',
            'laravel/cashier' => 'Cashier',
            'spatie/laravel-permission' => 'Permission',
            'spatie/laravel-activitylog' => 'Activity Log',
            'laravel/telescope' => 'Telescope',
            'laravel/horizon' => 'Horizon',
        ];

        foreach ($packageNames as $packageName => $displayName) {
            if ($this->packageExists($packageName, $composerLock)) {
                $packages[] = $displayName;
            }
        }

        return $packages;
    }

    public function hasExistingSpotifyConfig(): bool
    {
        // Check .env file
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            if (str_contains($envContent, 'SPOTIFY_CLIENT_ID')) {
                return true;
            }
        }

        // Check config file
        return File::exists(config_path('spotify-client.php'));
    }

    public function recommendAuthFlow(): string
    {
        $appType = $this->analysis['app_type'] ?? $this->detectAppType();

        return match ($appType) {
            'api-only' => 'client_credentials',
            'spa' => 'authorization_code',
            'web-app' => 'authorization_code',
            default => 'client_credentials',
        };
    }

    public function suggestAppName(): string
    {
        $appName = $this->getAppName();

        if ($appName && $appName !== 'Laravel') {
            return "{$appName} Spotify Integration";
        }

        return 'My Laravel Spotify App';
    }

    public function suggestIntegrations(): array
    {
        $suggestions = [];
        $packages = $this->analysis['packages'] ?? $this->detectInstalledPackages();

        if (in_array('Livewire', $packages)) {
            $suggestions['livewire'] = 'Livewire components for playlist management';
        }

        if (in_array('Inertia.js', $packages)) {
            $suggestions['inertia'] = 'Inertia.js components for music player';
        }

        if (in_array('Filament', $packages)) {
            $suggestions['filament'] = 'Filament resources for Spotify data';
        }

        if ($this->hasQueueSupport()) {
            $suggestions['jobs'] = 'Background jobs for playlist synchronization';
        }

        if ($this->hasCacheSupport()) {
            $suggestions['cache'] = 'Advanced caching for API responses';
        }

        return $suggestions;
    }

    public function backupEnvironment(): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            return;
        }

        $timestamp = now()->format('Y-m-d-H-i-s');
        $backupPath = base_path(".env.backup.{$timestamp}");

        File::copy($envPath, $backupPath);

        // Add backup files to .gitignore if not present
        $this->addToGitignore(['.env.backup.*']);
    }

    public function addCredentials(array $appConfig, array $tokens): void
    {
        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : '';

        $credentialLines = [
            "SPOTIFY_CLIENT_ID={$appConfig['client_id']}",
            "SPOTIFY_CLIENT_SECRET={$appConfig['client_secret']}",
        ];

        if (isset($tokens['access_token'])) {
            $credentialLines[] = "SPOTIFY_ACCESS_TOKEN={$tokens['access_token']}";
        }

        if (isset($tokens['refresh_token'])) {
            $credentialLines[] = "SPOTIFY_REFRESH_TOKEN={$tokens['refresh_token']}";
        }

        // Remove existing Spotify credentials
        $envContent = preg_replace('/^SPOTIFY_.*$/m', '', $envContent);
        $envContent = preg_replace('/\n\n+/', "\n\n", $envContent);

        // Add new credentials at the end
        if (! str_ends_with($envContent, "\n")) {
            $envContent .= "\n";
        }

        $envContent .= "\n# Spotify API Configuration\n";
        $envContent .= implode("\n", $credentialLines)."\n";

        File::put($envPath, $envContent);
    }

    public function configureMarket(): void
    {
        $market = $this->detectUserMarket();
        $this->addToEnv("SPOTIFY_DEFAULT_MARKET={$market}");
    }

    public function configureCaching(): void
    {
        $this->addToEnv('SPOTIFY_CACHE_ENABLED=true');
        $this->addToEnv('SPOTIFY_CACHE_TTL=3600');
    }

    public function applySecurity(): void
    {
        $envPath = base_path('.env');

        if (File::exists($envPath)) {
            // Ensure .env is not world-readable
            chmod($envPath, 0640);
        }

        // Ensure .env is in .gitignore
        $this->addToGitignore(['.env', '.env.*']);
    }

    public function hasComplexAuth(): bool
    {
        return $this->recommendAuthFlow() === 'authorization_code';
    }

    public function hasIntegrations(): bool
    {
        return ! empty($this->suggestIntegrations());
    }

    private function isApiOnly(): bool
    {
        // Check if web routes are minimal
        $webRoutesPath = base_path('routes/web.php');
        if (! File::exists($webRoutesPath)) {
            return true;
        }

        $webRoutes = File::get($webRoutesPath);

        // Check for minimal web routes (just welcome route)
        $routeCount = substr_count($webRoutes, 'Route::');
        if ($routeCount <= 1) {
            return true;
        }

        // Check if API routes exist and are substantial
        $apiRoutesPath = base_path('routes/api.php');
        if (File::exists($apiRoutesPath)) {
            $apiRoutes = File::get($apiRoutesPath);
            $apiRouteCount = substr_count($apiRoutes, 'Route::');

            return $apiRouteCount > $routeCount;
        }

        return false;
    }

    private function isSpa(): bool
    {
        $packages = $this->detectInstalledPackages();

        // Check for SPA frameworks
        if (in_array('Inertia.js', $packages)) {
            return true;
        }

        // Check for Vue/React in package.json
        $packageJsonPath = base_path('package.json');
        if (File::exists($packageJsonPath)) {
            $packageJson = json_decode(File::get($packageJsonPath), true);
            $dependencies = array_merge(
                $packageJson['dependencies'] ?? [],
                $packageJson['devDependencies'] ?? []
            );

            return isset($dependencies['vue']) ||
                   isset($dependencies['react']) ||
                   isset($dependencies['@inertiajs/inertia']);
        }

        return false;
    }

    private function isWebApp(): bool
    {
        // Check for Blade views
        $viewsPath = base_path('resources/views');
        if (File::exists($viewsPath)) {
            $viewFiles = File::allFiles($viewsPath);

            return count($viewFiles) > 1; // More than just welcome.blade.php
        }

        return false;
    }

    private function getComposerLockContent(): ?array
    {
        $composerLockPath = base_path('composer.lock');

        if (! File::exists($composerLockPath)) {
            return null;
        }

        return json_decode(File::get($composerLockPath), true);
    }

    private function packageExists(string $packageName, array $composerLock): bool
    {
        $packages = array_merge(
            $composerLock['packages'] ?? [],
            $composerLock['packages-dev'] ?? []
        );

        foreach ($packages as $package) {
            if ($package['name'] === $packageName) {
                return true;
            }
        }

        return false;
    }

    private function analyzeEnvStructure(): array
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            return ['exists' => false];
        }

        $envContent = File::get($envPath);
        $lines = explode("\n", $envContent);

        return [
            'exists' => true,
            'line_count' => count($lines),
            'has_sections' => $this->hasEnvSections($envContent),
            'has_comments' => str_contains($envContent, '#'),
        ];
    }

    private function hasEnvSections(string $content): bool
    {
        return preg_match('/^#\s*[A-Z].*Configuration/m', $content) > 0;
    }

    private function getAppName(): ?string
    {
        return config('app.name');
    }

    private function getAppUrl(): ?string
    {
        return config('app.url');
    }

    private function getEnvironment(): string
    {
        return config('app.env', 'production');
    }

    private function hasQueueSupport(): bool
    {
        return config('queue.default') !== 'sync';
    }

    private function hasCacheSupport(): bool
    {
        return config('cache.default') !== 'array';
    }

    private function detectUserMarket(): string
    {
        // Try to detect user's country/market
        // This is a simplified version - in reality you might use IP geolocation
        $timezone = config('app.timezone');

        $timezoneToMarket = [
            'America/New_York' => 'US',
            'America/Chicago' => 'US',
            'America/Denver' => 'US',
            'America/Los_Angeles' => 'US',
            'Europe/London' => 'GB',
            'Europe/Paris' => 'FR',
            'Europe/Berlin' => 'DE',
            'Europe/Rome' => 'IT',
            'Europe/Madrid' => 'ES',
            'Australia/Sydney' => 'AU',
            'Asia/Tokyo' => 'JP',
            'America/Toronto' => 'CA',
        ];

        return $timezoneToMarket[$timezone] ?? 'US';
    }

    private function addToEnv(string $line): void
    {
        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : '';

        // Check if line already exists
        $key = explode('=', $line)[0];
        if (str_contains($envContent, $key)) {
            // Replace existing line
            $envContent = preg_replace("/^{$key}=.*$/m", $line, $envContent);
        } else {
            // Add new line
            if (! str_ends_with($envContent, "\n")) {
                $envContent .= "\n";
            }
            $envContent .= $line."\n";
        }

        File::put($envPath, $envContent);
    }

    private function addToGitignore(array $patterns): void
    {
        $gitignorePath = base_path('.gitignore');
        $gitignoreContent = File::exists($gitignorePath) ? File::get($gitignorePath) : '';

        $newPatterns = [];
        foreach ($patterns as $pattern) {
            if (! str_contains($gitignoreContent, $pattern)) {
                $newPatterns[] = $pattern;
            }
        }

        if (! empty($newPatterns)) {
            if (! str_ends_with($gitignoreContent, "\n")) {
                $gitignoreContent .= "\n";
            }
            $gitignoreContent .= implode("\n", $newPatterns)."\n";
            File::put($gitignorePath, $gitignoreContent);
        }
    }
}
