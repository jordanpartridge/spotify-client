<?php

use Jordanpartridge\SpotifyClient\Services\CredentialValidator;
use Jordanpartridge\SpotifyClient\SpotifyConnector;

describe('CredentialValidator Simple Tests', function () {
    test('it can be instantiated with connector', function () {
        $connector = new SpotifyConnector;
        $validator = new CredentialValidator($connector);

        expect($validator)->toBeInstanceOf(CredentialValidator::class);
    });

    test('it validates token expiration correctly', function () {
        $connector = new SpotifyConnector;
        $validator = new CredentialValidator($connector);

        // Test with future expiration
        $futureTokens = ['expires_at' => time() + 3600];
        $result = $validator->validateTokenExpiration($futureTokens);

        expect($result)
            ->toHaveKey('success', true)
            ->toHaveKey('message', 'Token is valid')
            ->toHaveKey('expires_in_seconds')
            ->toHaveKey('expires_in_minutes');
    });

    test('it detects expired tokens', function () {
        $connector = new SpotifyConnector;
        $validator = new CredentialValidator($connector);

        // Test with past expiration
        $expiredTokens = ['expires_at' => time() - 3600];
        $result = $validator->validateTokenExpiration($expiredTokens);

        expect($result)
            ->toHaveKey('success', false)
            ->toHaveKey('message', 'Token has expired')
            ->toHaveKey('expired_seconds_ago');
    });

    test('it handles missing expiration time', function () {
        $connector = new SpotifyConnector;
        $validator = new CredentialValidator($connector);

        $result = $validator->validateTokenExpiration([]);

        expect($result)
            ->toHaveKey('success', false)
            ->toHaveKey('message', 'No expiration time found in token');
    });
});
