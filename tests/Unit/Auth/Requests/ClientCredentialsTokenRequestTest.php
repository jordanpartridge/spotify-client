<?php

use Jordanpartridge\SpotifyClient\Auth\Requests\ClientCredentialsTokenRequest;
use Saloon\Enums\Method;

describe('ClientCredentialsTokenRequest', function () {
    test('it has the correct method', function () {
        $request = new ClientCredentialsTokenRequest('client_id', 'client_secret');

        expect($request->getMethod())->toBe(Method::POST);
    });

    test('it resolves the correct endpoint', function () {
        $request = new ClientCredentialsTokenRequest('client_id', 'client_secret');

        expect($request->resolveEndpoint())->toBe('/api/token');
    });

    test('it includes correct form data', function () {
        $clientId = 'test_client_id';
        $clientSecret = 'test_client_secret';
        $request = new ClientCredentialsTokenRequest($clientId, $clientSecret);

        $body = $request->body()->all();

        expect($body)->toEqual([
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);
    });

    test('it handles empty credentials', function () {
        $request = new ClientCredentialsTokenRequest('', '');

        $body = $request->body()->all();

        expect($body)->toEqual([
            'grant_type' => 'client_credentials',
            'client_id' => '',
            'client_secret' => '',
        ]);
    });
});
