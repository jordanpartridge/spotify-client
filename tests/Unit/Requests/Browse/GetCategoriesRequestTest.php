<?php

use Jordanpartridge\SpotifyClient\Requests\Browse\GetCategoriesRequest;
use Saloon\Enums\Method;

describe('GetCategoriesRequest', function () {
    test('it has the correct method', function () {
        $request = new GetCategoriesRequest;

        expect($request->getMethod())->toBe(Method::GET);
    });

    test('it resolves the correct endpoint', function () {
        $request = new GetCategoriesRequest;

        expect($request->resolveEndpoint())->toBe('/browse/categories');
    });

    test('it includes default pagination parameters', function () {
        $request = new GetCategoriesRequest;

        $query = $request->query()->all();

        expect($query)->toHaveKey('limit')
            ->and($query['limit'])->toBe(20)
            ->and($query)->toHaveKey('offset')
            ->and($query['offset'])->toBe(0);
    });

    test('it accepts custom limit and offset', function () {
        $request = new GetCategoriesRequest(50, 10);

        $query = $request->query()->all();

        expect($query['limit'])->toBe(50)
            ->and($query['offset'])->toBe(10);
    });

    test('it handles zero values for limit and offset', function () {
        $request = new GetCategoriesRequest(0, 0);

        $query = $request->query()->all();

        expect($query['limit'])->toBe(0)
            ->and($query['offset'])->toBe(0);
    });

    test('it handles large pagination values', function () {
        $request = new GetCategoriesRequest(50, 1000);

        $query = $request->query()->all();

        expect($query['limit'])->toBe(50)
            ->and($query['offset'])->toBe(1000);
    });
});
