<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Commands\Player;

use Illuminate\Console\Command;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\table;

class StatusCommand extends Command
{
    protected $signature = 'spotify:player:status';

    protected $description = 'Show current Spotify player status and now playing information';

    public function handle(): int
    {
        intro('🎵 Spotify Player Status');

        try {
            $spotify = app(SpotifyClientInterface::class);

            // Get current playback state
            $response = $spotify->player()->currentlyPlaying();

            if ($response->status() === 204) {
                info('😴 No active playback session');
                note('💡 Start playing music in your Spotify app and try again');

                return self::SUCCESS;
            }

            if (! $response->successful()) {
                error("❌ Failed to get player status: {$response->status()}");

                return self::FAILURE;
            }

            $playback = $response->json();

            if (! $playback || ! isset($playback['item'])) {
                info('😴 No track currently playing');

                return self::SUCCESS;
            }

            $this->displayPlaybackInfo($playback);
            $this->displayPlayerControls();

            outro('🎶 Player status retrieved successfully!');

            return self::SUCCESS;

        } catch (\Exception $e) {
            error("Player status failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function displayPlaybackInfo(array $playback): void
    {
        $track = $playback['item'];
        $isPlaying = $playback['is_playing'];
        $progress = $playback['progress_ms'] ?? 0;
        $duration = $track['duration_ms'] ?? 0;

        // Current track info
        info('🎵 Now Playing');
        table(
            ['Property', 'Value'],
            [
                ['Track', $track['name']],
                ['Artist', $track['artists'][0]['name']],
                ['Album', $track['album']['name']],
                ['Status', $isPlaying ? '▶️ Playing' : '⏸️ Paused'],
                ['Progress', $this->formatDuration($progress).' / '.$this->formatDuration($duration)],
                ['Volume', $playback['device']['volume_percent'].'%'],
                ['Device', $playback['device']['name']],
                ['Shuffle', $playback['shuffle_state'] ? '🔀 On' : '🔀 Off'],
                ['Repeat', $this->formatRepeatState($playback['repeat_state'])],
            ]
        );

        // Show track preview if available
        if (! empty($track['preview_url'])) {
            note("🎧 Preview: {$track['preview_url']}");
        }

        // Show Spotify link
        if (! empty($track['external_urls']['spotify'])) {
            note("🔗 Spotify: {$track['external_urls']['spotify']}");
        }
    }

    private function displayPlayerControls(): void
    {
        info('🎮 Available Controls');
        note('Use these commands to control playback:');
        note('  php artisan spotify:player:play     - Resume playback');
        note('  php artisan spotify:player:pause    - Pause playback');
        note('  php artisan spotify:player:next     - Skip to next track');
        note('  php artisan spotify:player:previous - Go to previous track');
        note('  php artisan spotify:player:volume   - Set volume');
    }

    private function formatDuration(int $milliseconds): string
    {
        $seconds = intval($milliseconds / 1000);
        $minutes = intval($seconds / 60);
        $seconds = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    private function formatRepeatState(string $repeatState): string
    {
        return match ($repeatState) {
            'off' => '🔁 Off',
            'track' => '🔂 Track',
            'context' => '🔁 Context',
            default => "🔁 {$repeatState}",
        };
    }
}
