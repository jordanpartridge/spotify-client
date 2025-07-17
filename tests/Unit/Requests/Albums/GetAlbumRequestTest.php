<?php

use Jordanpartridge\SpotifyClient\Requests\Albums\GetAlbumRequest;
use Saloon\Enums\Method;

describe('GetAlbumRequest', function () {
    test('it has the correct method', function () {
        $request = new GetAlbumRequest('test-album-id');

        expect($request->getMethod())->toBe(Method::GET);
    });

    test('it resolves the correct endpoint', function () {
        $request = new GetAlbumRequest('test-album-id');

        expect($request->resolveEndpoint())->toBe('/albums/test-album-id');
    });

    test('it accepts album id in constructor', function () {
        $albumId = 'spotify-album-123';
        $request = new GetAlbumRequest($albumId);

        expect($request->resolveEndpoint())->toBe("/albums/{$albumId}");
    });

    test('it handles special characters in album id', function () {
        $albumId = 'test_album-id.123';
        $request = new GetAlbumRequest($albumId);

        expect($request->resolveEndpoint())->toBe("/albums/{$albumId}");
    });
});
