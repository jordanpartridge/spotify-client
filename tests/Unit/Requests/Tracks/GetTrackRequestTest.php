<?php

use Jordanpartridge\SpotifyClient\Requests\Tracks\GetTrackRequest;
use Saloon\Enums\Method;

describe('GetTrackRequest', function () {
    test('it has the correct method', function () {
        $request = new GetTrackRequest('test-track-id');

        expect($request->getMethod())->toBe(Method::GET);
    });

    test('it resolves the correct endpoint', function () {
        $request = new GetTrackRequest('test-track-id');

        expect($request->resolveEndpoint())->toBe('/tracks/test-track-id');
    });

    test('it accepts track id in constructor', function () {
        $trackId = 'spotify-track-789';
        $request = new GetTrackRequest($trackId);

        expect($request->resolveEndpoint())->toBe("/tracks/{$trackId}");
    });
});
