<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Commands\Auth;

use Illuminate\Console\Command;
use Jordanpartridge\SpotifyClient\Auth\SpotifyAuthManager;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;

class RefreshCommand extends Command
{
    protected $signature = 'spotify:auth:refresh {--flow= : Specific auth flow to refresh (client_credentials, authorization_code)}';

    protected $description = 'Manually refresh Spotify authentication tokens';

    public function handle(): int
    {
        intro('🔄 Refreshing Spotify Authentication Tokens');

        try {
            $authManager = app('spotify.auth');
            $flowOption = $this->option('flow');

            if ($flowOption) {
                return $this->refreshSpecificFlow($authManager, $flowOption);
            }

            // Refresh all available flows
            return $this->refreshAllFlows($authManager);

        } catch (\Exception $e) {
            error("Token refresh failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function refreshSpecificFlow(SpotifyAuthManager $authManager, string $flow): int
    {
        info("🔄 Refreshing {$flow} tokens...");

        try {
            $authenticator = $authManager->driver($flow);

            $refreshed = spin(function () use ($authenticator) {
                $authenticator->refresh();

                return ! $authenticator->isExpired();
            }, "Refreshing {$flow} token...");

            if ($refreshed) {
                info("✅ {$flow} token refreshed successfully!");

                return self::SUCCESS;
            } else {
                error("❌ Failed to refresh {$flow} token");

                return self::FAILURE;
            }

        } catch (\Exception $e) {
            error("❌ Error refreshing {$flow}: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function refreshAllFlows(SpotifyAuthManager $authManager): int
    {
        $flows = ['client_credentials', 'authorization_code'];
        $successCount = 0;

        foreach ($flows as $flow) {
            try {
                info("🔄 Refreshing {$flow} tokens...");

                $authenticator = $authManager->driver($flow);

                $refreshed = spin(function () use ($authenticator) {
                    $authenticator->refresh();

                    return ! $authenticator->isExpired();
                }, "Refreshing {$flow} token...");

                if ($refreshed) {
                    info("✅ {$flow} token refreshed successfully!");
                    $successCount++;
                } else {
                    error("❌ Failed to refresh {$flow} token");
                }

            } catch (\Exception $e) {
                error("❌ Error refreshing {$flow}: {$e->getMessage()}");
            }
        }

        if ($successCount > 0) {
            outro("🎵 Token refresh complete! {$successCount}/{count($flows)} flows refreshed successfully.");

            return self::SUCCESS;
        } else {
            outro('❌ No tokens were successfully refreshed.');

            return self::FAILURE;
        }
    }
}
