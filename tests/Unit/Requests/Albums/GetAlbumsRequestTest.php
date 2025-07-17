<?php

use Jordanpartridge\SpotifyClient\Requests\Albums\GetAlbumsRequest;
use Saloon\Enums\Method;

describe('GetAlbumsRequest', function () {
    test('it has the correct method', function () {
        $request = new GetAlbumsRequest(['album1', 'album2']);

        expect($request->getMethod())->toBe(Method::GET);
    });

    test('it resolves the correct endpoint', function () {
        $request = new GetAlbumsRequest(['album1', 'album2']);

        expect($request->resolveEndpoint())->toBe('/albums');
    });

    test('it includes album ids in query parameters', function () {
        $albumIds = ['album1', 'album2', 'album3'];
        $request = new GetAlbumsRequest($albumIds);

        $query = $request->query()->all();

        expect($query)->toHaveKey('ids')
            ->and($query['ids'])->toBe('album1,album2,album3');
    });

    test('it handles single album id', function () {
        $request = new GetAlbumsRequest(['single-album']);

        $query = $request->query()->all();

        expect($query['ids'])->toBe('single-album');
    });

    test('it handles empty album ids array', function () {
        $request = new GetAlbumsRequest([]);

        $query = $request->query()->all();

        expect($query['ids'])->toBe('');
    });
});
