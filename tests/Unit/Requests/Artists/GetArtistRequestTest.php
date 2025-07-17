<?php

use Jordanpartridge\SpotifyClient\Requests\Artists\GetArtistRequest;
use Saloon\Enums\Method;

describe('GetArtistRequest', function () {
    test('it has the correct method', function () {
        $request = new GetArtistRequest('test-artist-id');

        expect($request->getMethod())->toBe(Method::GET);
    });

    test('it resolves the correct endpoint', function () {
        $request = new GetArtistRequest('test-artist-id');

        expect($request->resolveEndpoint())->toBe('/artists/test-artist-id');
    });

    test('it accepts artist id in constructor', function () {
        $artistId = 'spotify-artist-456';
        $request = new GetArtistRequest($artistId);

        expect($request->resolveEndpoint())->toBe("/artists/{$artistId}");
    });
});
