<?php

namespace Jordanpartridge\SpotifyClient\Tests;

use Jordanpartridge\SpotifyClient\SpotifyClientServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            SpotifyClientServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Set up test Spotify credentials
        config()->set('spotify-client.client_id', 'test_client_id');
        config()->set('spotify-client.client_secret', 'test_client_secret');
    }
}
