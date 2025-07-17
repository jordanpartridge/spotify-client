<?php

use Jordanpartridge\SpotifyClient\Services\EnvironmentDetector;

describe('EnvironmentDetector Simple Tests', function () {
    test('it can be instantiated', function () {
        $detector = new EnvironmentDetector;

        expect($detector)->toBeInstanceOf(EnvironmentDetector::class);
    });

    test('it detects app type', function () {
        $detector = new EnvironmentDetector;
        $appType = $detector->detectAppType();

        expect($appType)->toBeString();
        expect(in_array($appType, ['api-only', 'spa', 'web-app', 'unknown']))->toBeTrue();
    });

    test('it checks for complex auth', function () {
        $detector = new EnvironmentDetector;
        $hasComplexAuth = $detector->hasComplexAuth();

        expect($hasComplexAuth)->toBeBool();
    });

    test('it checks for integrations', function () {
        $detector = new EnvironmentDetector;
        $hasIntegrations = $detector->hasIntegrations();

        expect($hasIntegrations)->toBeBool();
    });

    test('it detects Laravel version', function () {
        $detector = new EnvironmentDetector;
        $version = $detector->detectLaravelVersion();

        expect($version === null || is_string($version))->toBeTrue();
    });

    test('it analyzes environment comprehensively', function () {
        $detector = new EnvironmentDetector;
        $analysis = $detector->analyzeEnvironment();

        expect($analysis)
            ->toBeArray()
            ->toHaveKey('app_type')
            ->toHaveKey('laravel_version')
            ->toHaveKey('packages')
            ->toHaveKey('existing_spotify_config')
            ->toHaveKey('env_structure')
            ->toHaveKey('app_name')
            ->toHaveKey('app_url')
            ->toHaveKey('environment');
    });
});
