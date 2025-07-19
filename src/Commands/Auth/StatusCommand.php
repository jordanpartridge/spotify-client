<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Commands\Auth;

use Illuminate\Console\Command;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\table;

class StatusCommand extends Command
{
    protected $signature = 'spotify:auth:status';

    protected $description = 'Check Spotify authentication status and configuration';

    public function handle(): int
    {
        intro('🔍 Spotify Authentication Status');

        try {
            // Check configuration
            $clientId = config('spotify-client.client_id');
            $clientSecret = config('spotify-client.client_secret');
            $authFlow = config('spotify-client.auth.default_flow', 'client_credentials');

            if (! $clientId || ! $clientSecret) {
                error('❌ Spotify credentials not configured');
                info('💡 Run `php artisan spotify:auth:setup` to configure authentication');

                return self::FAILURE;
            }

            // Display configuration status
            info('✅ Configuration Status');
            table(
                ['Setting', 'Status'],
                [
                    ['Client ID', $clientId ? '✅ Configured ('.substr($clientId, 0, 8).'...)' : '❌ Missing'],
                    ['Client Secret', $clientSecret ? '✅ Configured ('.substr($clientSecret, 0, 8).'...)' : '❌ Missing'],
                    ['Auth Flow', "✅ {$authFlow}"],
                    ['Token Storage', config('spotify-client.auth.token_storage.driver', 'file')],
                ]
            );

            // Test authentication
            info('🔑 Testing Authentication...');

            try {
                $spotify = app(SpotifyClientInterface::class);

                // Try a simple API call
                $testResponse = $spotify->search()->tracks('test', 1);

                if ($testResponse->successful()) {
                    info('✅ Authentication working correctly!');

                    // Show token info if available
                    $this->showTokenInfo();

                    outro('🎵 Spotify authentication is ready to rock!');

                    return self::SUCCESS;
                } else {
                    error('❌ Authentication test failed');
                    error("Response: {$testResponse->status()} - {$testResponse->body()}");

                    return self::FAILURE;
                }
            } catch (\Exception $e) {
                error("❌ Authentication error: {$e->getMessage()}");

                return self::FAILURE;
            }

        } catch (\Exception $e) {
            error("Status check failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function showTokenInfo(): void
    {
        $tokenPath = config('spotify-client.auth.token_storage.path');

        if (file_exists($tokenPath)) {
            $tokens = json_decode(file_get_contents($tokenPath), true);

            if ($tokens) {
                info('🎫 Token Information');

                foreach ($tokens as $type => $token) {
                    $expiresAt = $token['expires_at'] ?? null;
                    $isExpired = $expiresAt ? time() > $expiresAt : false;
                    $expiryStatus = $isExpired ? '🔴 Expired' : '🟢 Valid';

                    table(
                        ['Token Type', 'Status', 'Expires'],
                        [
                            [
                                $type,
                                $expiryStatus,
                                $expiresAt ? date('Y-m-d H:i:s', $expiresAt) : 'Unknown',
                            ],
                        ]
                    );
                }
            }
        }
    }
}
