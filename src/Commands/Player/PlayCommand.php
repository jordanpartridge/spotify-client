<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Commands\Player;

use Illuminate\Console\Command;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;

class PlayCommand extends Command
{
    protected $signature = 'spotify:player:play {--uri= : Spotify URI to play (track, album, playlist)}';

    protected $description = 'Start or resume Spotify playback';

    public function handle(): int
    {
        try {
            $spotify = app(SpotifyClientInterface::class);
            $uri = $this->option('uri');

            if ($uri) {
                return $this->playSpecificContent($spotify, $uri);
            }

            return $this->resumePlayback($spotify);

        } catch (\Exception $e) {
            error("Playback failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function playSpecificContent(SpotifyClientInterface $spotify, string $uri): int
    {
        info("🎵 Playing: {$uri}");

        $success = spin(function () use ($spotify, $uri) {
            $response = $spotify->player()->play(['uris' => [$uri]]);

            return $response->successful();
        }, 'Starting playback...');

        if ($success) {
            info('✅ Playback started successfully!');

            return self::SUCCESS;
        } else {
            error('❌ Failed to start playback');

            return self::FAILURE;
        }
    }

    private function resumePlayback(SpotifyClientInterface $spotify): int
    {
        info('▶️ Resuming playback...');

        $success = spin(function () use ($spotify) {
            $response = $spotify->player()->play();

            return $response->successful();
        }, 'Resuming playback...');

        if ($success) {
            info('✅ Playback resumed successfully!');

            return self::SUCCESS;
        } else {
            error('❌ Failed to resume playback');

            return self::FAILURE;
        }
    }
}
