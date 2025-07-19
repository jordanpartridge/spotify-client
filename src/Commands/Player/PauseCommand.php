<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Commands\Player;

use Illuminate\Console\Command;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;

class PauseCommand extends Command
{
    protected $signature = 'spotify:player:pause';

    protected $description = 'Pause Spotify playback';

    public function handle(): int
    {
        info('⏸️ Pausing playback...');

        try {
            $spotify = app(SpotifyClientInterface::class);

            $success = spin(function () use ($spotify) {
                $response = $spotify->player()->pause();

                return $response->successful();
            }, 'Pausing playback...');

            if ($success) {
                info('✅ Playback paused successfully!');

                return self::SUCCESS;
            } else {
                error('❌ Failed to pause playback');

                return self::FAILURE;
            }

        } catch (\Exception $e) {
            error("Pause failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
