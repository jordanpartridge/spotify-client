<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient;

use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SpotifyClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('spotify-client')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SpotifyConnector::class, function () {
            return new SpotifyConnector();
        });

        $this->app->singleton(SpotifyClientInterface::class, function ($app) {
            return new SpotifyClient($app->make(SpotifyConnector::class));
        });

        $this->app->alias(SpotifyClientInterface::class, 'spotify-client');
    }
}