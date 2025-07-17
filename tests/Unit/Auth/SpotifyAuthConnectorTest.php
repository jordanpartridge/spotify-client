<?php

use Jordanpartridge\SpotifyClient\Auth\SpotifyAuthConnector;

describe('SpotifyAuthConnector', function () {
    beforeEach(function () {
        $this->connector = new SpotifyAuthConnector;
    });

    test('it resolves the correct base URL', function () {
        expect($this->connector->resolveBaseUrl())->toBe('https://accounts.spotify.com');
    });

    test('it includes correct default headers', function () {
        $headers = $this->connector->headers()->all();

        expect($headers)->toHaveKey('Accept', 'application/json');
    });

    test('it can be instantiated', function () {
        expect($this->connector)->toBeInstanceOf(SpotifyAuthConnector::class);
    });
});
