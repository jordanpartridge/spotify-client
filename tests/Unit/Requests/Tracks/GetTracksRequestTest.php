<?php

use Jordanpartridge\SpotifyClient\Requests\Tracks\GetTracksRequest;
use Saloon\Enums\Method;

describe('GetTracksRequest', function () {
    test('it has the correct method', function () {
        $request = new GetTracksRequest(['track1', 'track2']);

        expect($request->getMethod())->toBe(Method::GET);
    });

    test('it resolves the correct endpoint', function () {
        $request = new GetTracksRequest(['track1', 'track2']);

        expect($request->resolveEndpoint())->toBe('/tracks');
    });

    test('it includes track ids in query parameters', function () {
        $trackIds = ['track1', 'track2', 'track3'];
        $request = new GetTracksRequest($trackIds);

        $query = $request->query()->all();

        expect($query)->toHaveKey('ids')
            ->and($query['ids'])->toBe('track1,track2,track3');
    });

    test('it handles single track id', function () {
        $request = new GetTracksRequest(['single-track']);

        $query = $request->query()->all();

        expect($query['ids'])->toBe('single-track');
    });

    test('it handles empty track ids array', function () {
        $request = new GetTracksRequest([]);

        $query = $request->query()->all();

        expect($query['ids'])->toBe('');
    });
});
