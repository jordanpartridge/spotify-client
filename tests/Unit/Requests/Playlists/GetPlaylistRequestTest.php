<?php

use Jordanpartridge\SpotifyClient\Requests\Playlists\GetPlaylistRequest;
use Saloon\Enums\Method;

describe('GetPlaylistRequest', function () {
    test('it has the correct method', function () {
        $request = new GetPlaylistRequest('test-playlist-id');

        expect($request->getMethod())->toBe(Method::GET);
    });

    test('it resolves the correct endpoint', function () {
        $request = new GetPlaylistRequest('test-playlist-id');

        expect($request->resolveEndpoint())->toBe('/playlists/test-playlist-id');
    });

    test('it accepts playlist id in constructor', function () {
        $playlistId = 'spotify-playlist-456';
        $request = new GetPlaylistRequest($playlistId);

        expect($request->resolveEndpoint())->toBe("/playlists/{$playlistId}");
    });

    test('it handles special characters in playlist id', function () {
        $playlistId = 'playlist-with-special:chars';
        $request = new GetPlaylistRequest($playlistId);

        expect($request->resolveEndpoint())->toBe("/playlists/{$playlistId}");
    });
});
