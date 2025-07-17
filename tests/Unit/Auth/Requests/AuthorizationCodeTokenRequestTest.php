<?php

use Jordanpartridge\SpotifyClient\Auth\Requests\AuthorizationCodeTokenRequest;
use Saloon\Enums\Method;

describe('AuthorizationCodeTokenRequest', function () {
    test('it has the correct method', function () {
        $request = new AuthorizationCodeTokenRequest(
            'auth_code',
            'http://localhost/callback',
            'client_id',
            'client_secret'
        );

        expect($request->getMethod())->toBe(Method::POST);
    });

    test('it resolves the correct endpoint', function () {
        $request = new AuthorizationCodeTokenRequest(
            'auth_code',
            'http://localhost/callback',
            'client_id',
            'client_secret'
        );

        expect($request->resolveEndpoint())->toBe('/api/token');
    });

    test('it includes correct form data', function () {
        $code = 'authorization_code_123';
        $redirectUri = 'http://localhost:8080/callback';
        $clientId = 'test_client_id';
        $clientSecret = 'test_client_secret';

        $request = new AuthorizationCodeTokenRequest($code, $redirectUri, $clientId, $clientSecret);

        $body = $request->body()->all();

        expect($body)->toEqual([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);
    });
});
