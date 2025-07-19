<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Commands\Library;

use Illuminate\Console\Command;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\progress;

class SyncCommand extends Command
{
    protected $signature = 'spotify:library:sync {--limit=50 : Number of tracks to sync}';

    protected $description = 'Sync your Spotify library data locally for analysis';

    public function handle(): int
    {
        intro('📚 Syncing Spotify Library');

        try {
            $spotify = app(SpotifyClientInterface::class);
            $limit = (int) $this->option('limit');

            $libraryData = $this->syncLibraryData($spotify, $limit);

            if ($libraryData) {
                $this->displayLibraryStats($libraryData);
                outro("🎵 Library sync complete! Synced {$libraryData['total_tracks']} tracks.");

                return self::SUCCESS;
            } else {
                error('❌ Library sync failed');

                return self::FAILURE;
            }

        } catch (\Exception $e) {
            error("Library sync failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function syncLibraryData(SpotifyClientInterface $spotify, int $limit): ?array
    {
        $tracks = [];
        $artists = [];
        $albums = [];
        $genres = [];

        $offset = 0;
        $batchSize = min($limit, 50);

        return progress(
            label: 'Syncing library data...',
            steps: ceil($limit / $batchSize),
            callback: function ($progress) use ($spotify, &$tracks, &$artists, &$albums, &$genres, &$offset, $batchSize, $limit) {
                while (count($tracks) < $limit) {
                    $response = $spotify->library()->getSavedTracks($batchSize, $offset);

                    if (! $response->successful()) {
                        return null;
                    }

                    $data = $response->json();
                    $items = $data['items'] ?? [];

                    if (empty($items)) {
                        break;
                    }

                    foreach ($items as $item) {
                        $track = $item['track'];
                        $tracks[] = $track;

                        // Collect artist data
                        foreach ($track['artists'] as $artist) {
                            $artists[$artist['id']] = $artist['name'];
                        }

                        // Collect album data
                        $albums[$track['album']['id']] = $track['album']['name'];
                    }

                    $offset += $batchSize;
                    $progress->advance();

                    if (count($tracks) >= $limit) {
                        break;
                    }
                }

                // Get genres from artists (sample a few)
                $sampleArtists = array_slice(array_keys($artists), 0, 10, true);
                foreach ($sampleArtists as $artistId) {
                    try {
                        $artistResponse = $spotify->artists()->get($artistId);
                        if ($artistResponse->successful()) {
                            $artistData = $artistResponse->json();
                            $genres = array_merge($genres, $artistData['genres'] ?? []);
                        }
                    } catch (\Exception $e) {
                        // Skip if error getting artist data
                    }
                }

                return [
                    'total_tracks' => count($tracks),
                    'unique_artists' => count($artists),
                    'unique_albums' => count($albums),
                    'genres' => array_unique($genres),
                    'tracks' => $tracks,
                    'artists' => $artists,
                    'albums' => $albums,
                ];
            }
        );
    }

    private function displayLibraryStats(array $libraryData): void
    {
        info('📊 Library Statistics');

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Tracks', $libraryData['total_tracks']],
                ['Unique Artists', $libraryData['unique_artists']],
                ['Unique Albums', $libraryData['unique_albums']],
                ['Genres Found', count($libraryData['genres'])],
            ]
        );

        if (! empty($libraryData['genres'])) {
            info('🎭 Top Genres Found:');
            $topGenres = array_slice($libraryData['genres'], 0, 10);
            foreach ($topGenres as $genre) {
                info("  • {$genre}");
            }
        }

        info('💡 Tip: Use this data to discover your music preferences and find similar artists!');
    }
}
