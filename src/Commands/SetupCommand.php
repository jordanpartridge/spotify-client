<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\info;

class SetupCommand extends Command
{
    protected $signature = 'spotify:setup';

    protected $description = 'Set up Spotify integration (alias for spotify:auth:setup)';

    public function handle(): int
    {
        info('🎵 Redirecting to Spotify authentication setup...');

        return $this->call('spotify:auth:setup');
    }
}
