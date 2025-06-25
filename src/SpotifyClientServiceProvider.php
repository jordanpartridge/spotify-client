<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient;

use Jordanpartridge\SpotifyClient\Commands\SpotifyInstallCommand;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;
use Jordanpartridge\SpotifyClient\Services\CredentialValidator;
use Jordanpartridge\SpotifyClient\Services\EnvironmentDetector;
use Jordanpartridge\SpotifyClient\Services\OAuthFlowHandler;
use Jordanpartridge\SpotifyClient\Services\SpotifyAppManager;
use Jordanpartridge\SpotifyClient\Services\CodeGenerator;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SpotifyClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('spotify-client')
            ->hasConfigFile()
            ->hasCommand(SpotifyInstallCommand::class);
    }

    public function packageRegistered(): void
    {
        // Register core services
        $this->app->singleton(SpotifyConnector::class, function () {
            return new SpotifyConnector();
        });

        $this->app->singleton(SpotifyClientInterface::class, function ($app) {
            return new SpotifyClient($app->make(SpotifyConnector::class));
        });

        // Register install command services
        $this->app->singleton(EnvironmentDetector::class);
        $this->app->singleton(SpotifyAppManager::class);
        $this->app->singleton(OAuthFlowHandler::class);
        $this->app->singleton(CredentialValidator::class);
        $this->app->singleton(CodeGenerator::class);

        $this->app->alias(SpotifyClientInterface::class, 'spotify-client');
    }
}