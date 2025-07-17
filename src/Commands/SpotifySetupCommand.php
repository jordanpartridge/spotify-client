<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Commands;

use Illuminate\Console\Command;

class SpotifySetupCommand extends Command
{
    protected $signature = 'spotify:setup';

    protected $description = 'Set up Spotify integration (alias for spotify:install)';

    public function handle(): int
    {
        $this->info('🎵 Redirecting to Spotify Client setup...');

        return $this->call('spotify:install');
    }
}
