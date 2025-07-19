<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient;

use Illuminate\Filesystem\Filesystem;
use Jordanpartridge\SpotifyClient\Auth\SpotifyAuthConnector;
use Jordanpartridge\SpotifyClient\Auth\SpotifyAuthManager;
use Jordanpartridge\SpotifyClient\Auth\Storage\FileTokenStorage;
use Jordanpartridge\SpotifyClient\Commands\Auth\RefreshCommand;
use Jordanpartridge\SpotifyClient\Commands\Auth\SetupCommand as AuthSetupCommand;
use Jordanpartridge\SpotifyClient\Commands\Auth\StatusCommand as AuthStatusCommand;
use Jordanpartridge\SpotifyClient\Commands\InstallCommand;
use Jordanpartridge\SpotifyClient\Commands\Library\SyncCommand;
use Jordanpartridge\SpotifyClient\Commands\Player\PauseCommand;
use Jordanpartridge\SpotifyClient\Commands\Player\PlayCommand;
use Jordanpartridge\SpotifyClient\Commands\Player\StatusCommand as PlayerStatusCommand;
use Jordanpartridge\SpotifyClient\Commands\SetupCommand;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;
use Jordanpartridge\SpotifyClient\Contracts\TokenStorageInterface;
use Jordanpartridge\SpotifyClient\Facades\SpotifyFacade;
use Jordanpartridge\SpotifyClient\Services\CodeGenerator;
use Jordanpartridge\SpotifyClient\Services\CredentialValidator;
use Jordanpartridge\SpotifyClient\Services\EnvironmentDetector;
use Jordanpartridge\SpotifyClient\Services\OAuthFlowHandler;
use Jordanpartridge\SpotifyClient\Services\SpotifyAppManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SpotifyClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('spotify-client')
            ->hasConfigFile()
            ->hasFacade(SpotifyFacade::class, 'Spotify')
            // Legacy commands (for backward compatibility)
            ->hasCommand(InstallCommand::class)
            ->hasCommand(SetupCommand::class)
            // New organized command structure
            ->hasCommand(AuthSetupCommand::class)
            ->hasCommand(AuthStatusCommand::class)
            ->hasCommand(RefreshCommand::class)
            ->hasCommand(PlayCommand::class)
            ->hasCommand(PauseCommand::class)
            ->hasCommand(PlayerStatusCommand::class)
            ->hasCommand(SyncCommand::class);
    }

    public function packageRegistered(): void
    {
        // Register token storage
        $this->app->singleton(TokenStorageInterface::class, function ($app) {
            $driver = config('spotify-client.auth.token_storage.driver', 'file');

            return $this->createTokenStorage($driver);
        });

        // Register authentication manager
        $this->app->singleton('spotify.auth', function ($app) {
            return new SpotifyAuthManager($app);
        });

        // Register core services
        $this->app->singleton(SpotifyConnector::class, function ($app) {
            $authenticator = null;

            if (config('spotify-client.client_id') && config('spotify-client.client_secret')) {
                $authenticator = $app->make('spotify.auth')->driver();
            }

            return new SpotifyConnector($authenticator);
        });

        $this->app->singleton(SpotifyAuthConnector::class, function () {
            return new SpotifyAuthConnector;
        });

        $this->app->singleton(SpotifyClientInterface::class, function ($app) {
            $connector = $app->make(SpotifyConnector::class);
            $authenticator = null;

            if (config('spotify-client.client_id') && config('spotify-client.client_secret')) {
                $authenticator = $app->make('spotify.auth')->driver();
            }

            return new SpotifyClient($connector, $authenticator);
        });

        // Register install command services
        $this->app->singleton(EnvironmentDetector::class);
        $this->app->singleton(SpotifyAppManager::class);
        $this->app->singleton(OAuthFlowHandler::class);
        $this->app->singleton(CredentialValidator::class);
        $this->app->singleton(CodeGenerator::class);

        $this->app->alias(SpotifyClientInterface::class, 'spotify-client');
    }

    private function createTokenStorage(string $driver): TokenStorageInterface
    {
        return match ($driver) {
            'file' => new FileTokenStorage(
                $this->app->make(Filesystem::class),
                config('spotify-client.auth.token_storage.path', $this->getDefaultTokenStoragePath())
            ),
            default => throw new \InvalidArgumentException("Unsupported token storage driver: {$driver}")
        };
    }

    private function getDefaultTokenStoragePath(): string
    {
        // Check if we're in a Laravel application context
        if (function_exists('storage_path')) {
            return storage_path('app/spotify-tokens.json');
        }

        // Fallback for package development or non-Laravel environments
        return sys_get_temp_dir().'/spotify-tokens.json';
    }
}
