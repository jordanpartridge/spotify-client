<?php

use Jordanpartridge\SpotifyClient\Requests\Artists\GetArtistsRequest;
use Saloon\Enums\Method;

describe('GetArtistsRequest', function () {
    test('it has the correct method', function () {
        $request = new GetArtistsRequest(['artist1', 'artist2']);

        expect($request->getMethod())->toBe(Method::GET);
    });

    test('it resolves the correct endpoint', function () {
        $request = new GetArtistsRequest(['artist1', 'artist2']);

        expect($request->resolveEndpoint())->toBe('/artists');
    });

    test('it includes artist ids in query parameters', function () {
        $artistIds = ['artist1', 'artist2', 'artist3'];
        $request = new GetArtistsRequest($artistIds);

        $query = $request->query()->all();

        expect($query)->toHaveKey('ids')
            ->and($query['ids'])->toBe('artist1,artist2,artist3');
    });

    test('it handles single artist id', function () {
        $request = new GetArtistsRequest(['single-artist']);

        $query = $request->query()->all();

        expect($query['ids'])->toBe('single-artist');
    });

    test('it handles empty artist ids array', function () {
        $request = new GetArtistsRequest([]);

        $query = $request->query()->all();

        expect($query['ids'])->toBe('');
    });
});
