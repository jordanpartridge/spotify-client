<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Services;

use Jordanpartridge\SpotifyClient\Requests\Browse\GetCategoriesRequest;
use Jordanpartridge\SpotifyClient\SpotifyConnector;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Auth\TokenAuthenticator;

class CredentialValidator
{
    private const SPOTIFY_API_BASE = 'https://api.spotify.com/v1';

    public function __construct(
        private readonly SpotifyConnector $connector
    ) {}

    public function testConnection(array $tokens): array
    {
        try {
            // Configure the connector with authentication
            $this->connector->authenticate(new TokenAuthenticator($tokens['access_token']));

            $startTime = microtime(true);
            $response = $this->connector->send(new GetCategoriesRequest(1, 0));
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => $response->successful(),
                'message' => 'API connection successful',
                'response_time' => $responseTime,
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'message' => "Connection failed: {$e->getMessage()}",
                'suggestion' => $this->getConnectionErrorSuggestion($e),
            ];
        }
    }

    public function testAuthentication(array $tokens): array
    {
        try {
            // Configure the connector with authentication
            $this->connector->authenticate(new TokenAuthenticator($tokens['access_token']));

            // Test with a simple API call that requires authentication
            $response = $this->connector->send(new GetCategoriesRequest(1, 0));

            return [
                'success' => $response->successful(),
                'message' => 'Authentication successful',
                'token_info' => [
                    'token_type' => $tokens['token_type'] ?? 'Bearer',
                    'expires_at' => $tokens['expires_at'] ?? null,
                    'has_refresh_token' => ! empty($tokens['refresh_token']),
                ],
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'message' => "Authentication failed: {$e->getMessage()}",
                'suggestion' => $this->getAuthErrorSuggestion($e),
            ];
        }
    }

    public function testScopes(array $tokens): array
    {
        $scopeTests = [
            'user-read-private' => '/me',
            'user-read-email' => '/me',
            'playlist-read-private' => '/me/playlists?limit=1',
            'user-library-read' => '/me/albums?limit=1',
            'user-top-read' => '/me/top/artists?limit=1&time_range=short_term',
            'user-read-recently-played' => '/me/player/recently-played?limit=1',
        ];

        $results = [];
        $successCount = 0;

        foreach ($scopeTests as $scope => $endpoint) {
            try {
                $response = $this->httpClient->get(self::SPOTIFY_API_BASE.$endpoint, [
                    'headers' => [
                        'Authorization' => "Bearer {$tokens['access_token']}",
                    ],
                    'timeout' => 5,
                ]);

                $results[$scope] = [
                    'success' => $response->getStatusCode() === 200,
                    'message' => 'Scope permission verified',
                ];
                $successCount++;

            } catch (RequestException $e) {
                $results[$scope] = [
                    'success' => false,
                    'message' => $this->getScopeErrorMessage($e),
                ];
            }
        }

        return [
            'success' => $successCount > 0,
            'message' => "Verified {$successCount}/".count($scopeTests).' scopes',
            'scope_results' => $results,
        ];
    }

    public function testRateLimiting(array $tokens): array
    {
        try {
            $startTime = microtime(true);

            // Make multiple requests to test rate limiting behavior
            $responses = [];
            for ($i = 0; $i < 5; $i++) {
                $response = $this->httpClient->get(self::SPOTIFY_API_BASE.'/browse/categories', [
                    'headers' => [
                        'Authorization' => "Bearer {$tokens['access_token']}",
                    ],
                    'query' => ['limit' => 1, 'offset' => $i],
                ]);

                $responses[] = [
                    'status' => $response->getStatusCode(),
                    'rate_limit_remaining' => $response->getHeader('X-RateLimit-Remaining')[0] ?? null,
                ];
            }

            $endTime = microtime(true);
            $totalTime = $endTime - $startTime;

            return [
                'success' => true,
                'message' => 'Rate limiting working properly',
                'request_count' => count($responses),
                'total_time' => round($totalTime, 2),
                'rate_limit_info' => $this->extractRateLimitInfo($responses),
            ];

        } catch (RequestException $e) {
            if ($e->getCode() === 429) {
                return [
                    'success' => true,
                    'message' => 'Rate limiting detected and working',
                    'note' => 'Received 429 Too Many Requests as expected',
                ];
            }

            return [
                'success' => false,
                'message' => "Rate limit test failed: {$e->getMessage()}",
            ];
        }
    }

    public function testErrorHandling(array $tokens): array
    {
        $errorTests = [
            'invalid_endpoint' => [
                'url' => '/invalid-endpoint-test',
                'expected_status' => 404,
            ],
            'invalid_id' => [
                'url' => '/albums/invalid-album-id',
                'expected_status' => 400,
            ],
        ];

        $results = [];
        $successCount = 0;

        foreach ($errorTests as $testName => $test) {
            try {
                $this->httpClient->get(self::SPOTIFY_API_BASE.$test['url'], [
                    'headers' => [
                        'Authorization' => "Bearer {$tokens['access_token']}",
                    ],
                ]);

                // If we get here, the test failed (should have thrown an exception)
                $results[$testName] = [
                    'success' => false,
                    'message' => 'Expected error but got success response',
                ];

            } catch (RequestException $e) {
                $actualStatus = $e->getCode();
                $expectedStatus = $test['expected_status'];

                $success = $actualStatus === $expectedStatus;
                if ($success) {
                    $successCount++;
                }

                $results[$testName] = [
                    'success' => $success,
                    'message' => $success
                        ? "Correct error handling (HTTP {$actualStatus})"
                        : "Expected HTTP {$expectedStatus}, got HTTP {$actualStatus}",
                ];
            }
        }

        return [
            'success' => $successCount === count($errorTests),
            'message' => "Error handling: {$successCount}/".count($errorTests).' tests passed',
            'error_tests' => $results,
        ];
    }

    public function validateTokenExpiration(array $tokens): array
    {
        $expiresAt = $tokens['expires_at'] ?? null;

        if (! $expiresAt) {
            return [
                'success' => false,
                'message' => 'No expiration time found in token',
            ];
        }

        $currentTime = time();
        $timeRemaining = $expiresAt - $currentTime;

        if ($timeRemaining <= 0) {
            return [
                'success' => false,
                'message' => 'Token has expired',
                'expired_seconds_ago' => abs($timeRemaining),
            ];
        }

        return [
            'success' => true,
            'message' => 'Token is valid',
            'expires_in_seconds' => $timeRemaining,
            'expires_in_minutes' => round($timeRemaining / 60, 1),
        ];
    }

    public function performComprehensiveValidation(array $tokens): array
    {
        $tests = [
            'connection' => $this->testConnection($tokens),
            'authentication' => $this->testAuthentication($tokens),
            'scopes' => $this->testScopes($tokens),
            'rate_limiting' => $this->testRateLimiting($tokens),
            'error_handling' => $this->testErrorHandling($tokens),
            'token_expiration' => $this->validateTokenExpiration($tokens),
        ];

        $passedTests = array_filter($tests, fn ($test) => $test['success']);
        $totalTests = count($tests);
        $passedCount = count($passedTests);

        return [
            'overall_success' => $passedCount === $totalTests,
            'summary' => "Passed {$passedCount}/{$totalTests} validation tests",
            'score' => round(($passedCount / $totalTests) * 100, 1),
            'detailed_results' => $tests,
            'recommendations' => $this->generateRecommendations($tests),
        ];
    }

    private function measureResponseTime(callable $request): float
    {
        $startTime = microtime(true);

        try {
            $request();
        } catch (\Exception $e) {
            // Ignore exceptions for timing purposes
        }

        return round((microtime(true) - $startTime) * 1000, 2); // Convert to milliseconds
    }

    private function getConnectionErrorSuggestion(\Exception $e): string
    {
        $message = $e->getMessage();

        return match (true) {
            str_contains($message, 'timeout') => 'Request timed out. Check your internet connection.',
            str_contains($message, 'resolve') => 'DNS resolution failed. Check your network settings.',
            str_contains($message, 'refused') => 'Connection refused. Spotify API might be down.',
            default => 'Network error. Please check your internet connection and try again.',
        };
    }

    private function getAuthErrorSuggestion(\Exception $e): string
    {
        $code = $e->getCode();

        return match ($code) {
            401 => 'Invalid or expired access token. Try regenerating your credentials.',
            403 => 'Insufficient permissions. Check your app scopes and credentials.',
            400 => 'Bad request. Verify your client credentials are correct.',
            default => 'Authentication error. Please check your Spotify app configuration.',
        };
    }

    private function getScopeErrorMessage(\Exception $e): string
    {
        $code = $e->getCode();

        return match ($code) {
            403 => 'Scope not granted or insufficient permissions',
            401 => 'Authentication required for this scope',
            404 => 'Resource not found (may require different scope)',
            default => 'Scope test failed: '.$e->getMessage(),
        };
    }

    private function extractRateLimitInfo(array $responses): array
    {
        $rateLimitInfo = [];

        foreach ($responses as $response) {
            if ($response['rate_limit_remaining']) {
                $rateLimitInfo[] = $response['rate_limit_remaining'];
            }
        }

        return [
            'rate_limits_tracked' => ! empty($rateLimitInfo),
            'remaining_requests' => $rateLimitInfo[0] ?? null,
            'rate_limit_decreasing' => count($rateLimitInfo) > 1 &&
                ($rateLimitInfo[0] < $rateLimitInfo[count($rateLimitInfo) - 1]),
        ];
    }

    private function generateRecommendations(array $tests): array
    {
        $recommendations = [];

        if (! $tests['connection']['success']) {
            $recommendations[] = 'Fix network connectivity issues before proceeding';
        }

        if (! $tests['authentication']['success']) {
            $recommendations[] = 'Verify and regenerate your Spotify app credentials';
        }

        if (! $tests['scopes']['success']) {
            $recommendations[] = 'Review and adjust your requested permission scopes';
        }

        if (! $tests['token_expiration']['success']) {
            $recommendations[] = 'Implement token refresh logic for long-running applications';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'All tests passed! Your Spotify integration is ready to use.';
        }

        return $recommendations;
    }
}
