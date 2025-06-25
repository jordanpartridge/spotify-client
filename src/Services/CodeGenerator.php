<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CodeGenerator
{
    public function __construct(
        private readonly EnvironmentDetector $environmentDetector
    ) {}

    public function generateExamples(): void
    {
        $analysis = $this->environmentDetector->analyzeEnvironment();
        
        $this->createExamplesDirectory();
        $this->generateBasicController();
        $this->generateApiController();
        
        if (in_array('Livewire', $analysis['packages'])) {
            $this->generateLivewireComponent();
        }
        
        if (in_array('Inertia.js', $analysis['packages'])) {
            $this->generateInertiaController();
        }
        
        $this->generateUsageDocumentation($analysis);
        $this->generateRouteExamples($analysis);
    }

    public function generateIntegration(string $integration): void
    {
        match($integration) {
            'livewire' => $this->generateLivewireComponent(),
            'inertia' => $this->generateInertiaController(),
            'filament' => $this->generateFilamentResource(),
            'jobs' => $this->generateBackgroundJobs(),
            'cache' => $this->generateCacheExamples(),
            default => null,
        };
    }

    private function createExamplesDirectory(): void
    {
        $examplesPath = base_path('spotify-examples');
        
        if (!File::exists($examplesPath)) {
            File::makeDirectory($examplesPath, 0755, true);
        }
    }

    private function generateBasicController(): void
    {
        $controller = $this->generateControllerCode('SpotifyController', [
            'getAlbum' => $this->generateGetAlbumMethod(),
            'searchTracks' => $this->generateSearchTracksMethod(),
            'getArtist' => $this->generateGetArtistMethod(),
        ]);

        File::put(
            base_path('spotify-examples/SpotifyController.php'),
            $controller
        );
    }

    private function generateApiController(): void
    {
        $controller = $this->generateControllerCode('Api\SpotifyApiController', [
            'albums' => $this->generateApiAlbumsMethod(),
            'search' => $this->generateApiSearchMethod(),
            'recommendations' => $this->generateApiRecommendationsMethod(),
        ], true);

        $apiDir = base_path('spotify-examples/Api');
        if (!File::exists($apiDir)) {
            File::makeDirectory($apiDir, 0755, true);
        }

        File::put(
            base_path('spotify-examples/Api/SpotifyApiController.php'),
            $controller
        );
    }

    private function generateLivewireComponent(): void
    {
        $component = $this->generateLivewireComponentCode();
        $view = $this->generateLivewireViewCode();

        File::put(
            base_path('spotify-examples/PlaylistManager.php'),
            $component
        );

        File::put(
            base_path('spotify-examples/playlist-manager.blade.php'),
            $view
        );
    }

    private function generateInertiaController(): void
    {
        $controller = $this->generateControllerCode('SpotifyInertiaController', [
            'dashboard' => $this->generateInertiaDashboardMethod(),
            'playlists' => $this->generateInertiaPlaylistsMethod(),
        ]);

        File::put(
            base_path('spotify-examples/SpotifyInertiaController.php'),
            $controller
        );
    }

    private function generateFilamentResource(): void
    {
        $resource = <<<'PHP'
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpotifyPlaylistResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

class SpotifyPlaylistResource extends Resource
{
    protected static ?string $model = null; // Using API data, not Eloquent
    protected static ?string $navigationIcon = 'heroicon-o-musical-note';
    protected static ?string $navigationLabel = 'Spotify Playlists';

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => collect(app(SpotifyClientInterface::class)->playlists()->getAll()))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Playlist Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tracks.total')
                    ->label('Tracks')
                    ->numeric(),
                Tables\Columns\TextColumn::make('owner.display_name')
                    ->label('Owner'),
                Tables\Columns\BooleanColumn::make('public')
                    ->label('Public'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => $record['external_urls']['spotify']),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpotifyPlaylists::route('/'),
        ];
    }
}
PHP;

        File::put(
            base_path('spotify-examples/SpotifyPlaylistResource.php'),
            $resource
        );
    }

    private function generateBackgroundJobs(): void
    {
        $job = <<<'PHP'
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

class SyncSpotifyPlaylistsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $userId
    ) {}

    public function handle(SpotifyClientInterface $spotify): void
    {
        $playlists = $spotify->users()->getPlaylists($this->userId);
        
        foreach ($playlists['items'] as $playlist) {
            // Process each playlist
            $this->processPlaylist($playlist);
        }
    }

    private function processPlaylist(array $playlist): void
    {
        // Your playlist processing logic here
        // e.g., cache playlist data, sync with database, etc.
    }
}
PHP;

        File::put(
            base_path('spotify-examples/SyncSpotifyPlaylistsJob.php'),
            $job
        );
    }

    private function generateCacheExamples(): void
    {
        $service = <<<'PHP'
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

class CachedSpotifyService
{
    public function __construct(
        private readonly SpotifyClientInterface $spotify
    ) {}

    public function getAlbumWithCache(string $albumId): array
    {
        return Cache::remember(
            "spotify.album.{$albumId}",
            now()->addHour(),
            fn () => $this->spotify->albums()->get($albumId)->json()
        );
    }

    public function searchTracksWithCache(string $query): array
    {
        $cacheKey = 'spotify.search.' . md5($query);
        
        return Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            fn () => $this->spotify->search()
                ->tracks($query)
                ->limit(20)
                ->get()
                ->json()
        );
    }

    public function getPopularTracksWithCache(string $artistId): array
    {
        return Cache::remember(
            "spotify.artist.{$artistId}.top-tracks",
            now()->addDay(),
            fn () => $this->spotify->artists()->getTopTracks($artistId)->json()
        );
    }
}
PHP;

        File::put(
            base_path('spotify-examples/CachedSpotifyService.php'),
            $service
        );
    }

    private function generateUsageDocumentation(array $analysis): void
    {
        $appName = $analysis['app_name'] ?? 'Your Laravel App';
        $packages = implode(', ', $analysis['packages']);
        
        $documentation = <<<MD
# Spotify Integration for {$appName}

## Setup Complete! 🎉

Your Laravel application is now integrated with the Spotify Web API.

### Detected Configuration
- **Laravel Version**: {$analysis['laravel_version']}
- **App Type**: {$analysis['app_type']}
- **Packages**: {$packages}

## Basic Usage

### Getting an Album
```php
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

public function getAlbum(SpotifyClientInterface \$spotify, string \$id)
{
    \$album = \$spotify->albums()->get(\$id);
    return \$album->json();
}
```

### Searching for Tracks
```php
\$results = \$spotify->search()
    ->tracks('your search query')
    ->limit(20)
    ->get();
```

### Getting Artist Information
```php
\$artist = \$spotify->artists()->get(\$artistId);
\$topTracks = \$spotify->artists()->getTopTracks(\$artistId);
```

## Generated Examples

The following example files have been created in your `spotify-examples/` directory:

- `SpotifyController.php` - Basic web controller examples
- `Api/SpotifyApiController.php` - API endpoint examples
MD;

        if (in_array('Livewire', $analysis['packages'])) {
            $documentation .= "\n- `PlaylistManager.php` - Livewire component for playlist management";
        }

        if (in_array('Inertia.js', $analysis['packages'])) {
            $documentation .= "\n- `SpotifyInertiaController.php` - Inertia.js integration examples";
        }

        $documentation .= <<<MD

## Next Steps

1. **Copy examples to your app**: Move the generated files to your `app/` directory
2. **Add routes**: Use the route examples in `routes.example.php`
3. **Customize**: Modify the examples to fit your specific needs
4. **Test**: Use the generated controllers to test your integration

## Configuration

Your Spotify credentials are stored in your `.env` file:
```
SPOTIFY_CLIENT_ID=your_client_id
SPOTIFY_CLIENT_SECRET=your_client_secret
SPOTIFY_DEFAULT_MARKET=US
```

## Support

- 📚 [Package Documentation](https://github.com/jordanpartridge/spotify-client)
- 🐛 [Report Issues](https://github.com/jordanpartridge/spotify-client/issues)
- 💬 Need help? Open an issue on GitHub!

Enjoy building with Spotify! 🎵
MD;

        File::put(
            base_path('spotify-examples/README.md'),
            $documentation
        );
    }

    private function generateRouteExamples(array $analysis): void
    {
        $routes = <<<'PHP'
<?php

use App\Http\Controllers\SpotifyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Spotify API Routes
|--------------------------------------------------------------------------
|
| Example routes for your Spotify integration. Copy these to your
| routes/web.php or routes/api.php file and modify as needed.
|
*/

// Web Routes
Route::prefix('spotify')->group(function () {
    Route::get('/album/{id}', [SpotifyController::class, 'getAlbum']);
    Route::get('/artist/{id}', [SpotifyController::class, 'getArtist']);
    Route::get('/search', [SpotifyController::class, 'searchTracks']);
});

// API Routes
Route::prefix('api/spotify')->group(function () {
    Route::get('/albums/{id}', [Api\SpotifyApiController::class, 'albums']);
    Route::get('/search', [Api\SpotifyApiController::class, 'search']);
    Route::get('/recommendations', [Api\SpotifyApiController::class, 'recommendations']);
});
PHP;

        if (in_array('Livewire', $analysis['packages'])) {
            $routes .= <<<'PHP'

// Livewire Routes
Route::get('/playlists', function () {
    return view('livewire.playlist-manager');
});
PHP;
        }

        File::put(
            base_path('spotify-examples/routes.example.php'),
            $routes
        );
    }

    private function generateControllerCode(string $className, array $methods, bool $isApi = false): string
    {
        $namespace = $isApi ? 'App\Http\Controllers\Api' : 'App\Http\Controllers';
        $use = $isApi ? 'use Illuminate\Http\JsonResponse;' : '';
        
        $methodsCode = implode("\n\n", array_values($methods));

        return <<<PHP
<?php

namespace {$namespace};

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
{$use}
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

class {$className} extends Controller
{
    public function __construct(
        private readonly SpotifyClientInterface \$spotify
    ) {}

{$methodsCode}
}
PHP;
    }

    private function generateGetAlbumMethod(): string
    {
        return <<<'PHP'
    public function getAlbum(string $id)
    {
        try {
            $album = $this->spotify->albums()->get($id);
            
            return view('spotify.album', [
                'album' => $album->json(),
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Album not found');
        }
    }
PHP;
    }

    private function generateSearchTracksMethod(): string
    {
        return <<<'PHP'
    public function searchTracks(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return view('spotify.search');
        }

        try {
            $results = $this->spotify->search()
                ->tracks($query)
                ->limit(20)
                ->get();
                
            return view('spotify.search', [
                'query' => $query,
                'results' => $results->json(),
            ]);
        } catch (\Exception $e) {
            return view('spotify.search', [
                'query' => $query,
                'error' => 'Search failed. Please try again.',
            ]);
        }
    }
PHP;
    }

    private function generateGetArtistMethod(): string
    {
        return <<<'PHP'
    public function getArtist(string $id)
    {
        try {
            $artist = $this->spotify->artists()->get($id);
            $topTracks = $this->spotify->artists()->getTopTracks($id);
            
            return view('spotify.artist', [
                'artist' => $artist->json(),
                'topTracks' => $topTracks->json(),
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Artist not found');
        }
    }
PHP;
    }

    private function generateApiAlbumsMethod(): string
    {
        return <<<'PHP'
    public function albums(string $id): JsonResponse
    {
        try {
            $album = $this->spotify->albums()->get($id);
            
            return response()->json([
                'success' => true,
                'data' => $album->json(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Album not found',
            ], 404);
        }
    }
PHP;
    }

    private function generateApiSearchMethod(): string
    {
        return <<<'PHP'
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');
        $type = $request->get('type', 'track');
        $limit = $request->get('limit', 20);

        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Query parameter is required',
            ], 400);
        }

        try {
            $results = $this->spotify->search()
                ->{$type}($query)
                ->limit($limit)
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $results->json(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
            ], 500);
        }
    }
PHP;
    }

    private function generateApiRecommendationsMethod(): string
    {
        return <<<'PHP'
    public function recommendations(Request $request): JsonResponse
    {
        $seedTracks = $request->get('seed_tracks');
        $seedArtists = $request->get('seed_artists');
        
        if (!$seedTracks && !$seedArtists) {
            return response()->json([
                'success' => false,
                'message' => 'At least one seed parameter is required',
            ], 400);
        }

        try {
            $recommendations = $this->spotify->recommendations()
                ->seedTracks($seedTracks)
                ->seedArtists($seedArtists)
                ->limit(20)
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $recommendations->json(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get recommendations',
            ], 500);
        }
    }
PHP;
    }

    private function generateLivewireComponentCode(): string
    {
        return <<<'PHP'
<?php

namespace App\Livewire;

use Livewire\Component;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

class PlaylistManager extends Component
{
    public array $playlists = [];
    public string $search = '';
    public bool $loading = false;

    public function __construct(
        private readonly SpotifyClientInterface $spotify
    ) {}

    public function mount()
    {
        $this->loadPlaylists();
    }

    public function loadPlaylists()
    {
        $this->loading = true;
        
        try {
            $response = $this->spotify->playlists()->getAll();
            $this->playlists = $response->json()['items'] ?? [];
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load playlists');
        }
        
        $this->loading = false;
    }

    public function searchPlaylists()
    {
        if (empty($this->search)) {
            $this->loadPlaylists();
            return;
        }

        $this->loading = true;
        
        try {
            $response = $this->spotify->search()
                ->playlists($this->search)
                ->limit(20)
                ->get();
                
            $this->playlists = $response->json()['playlists']['items'] ?? [];
        } catch (\Exception $e) {
            session()->flash('error', 'Search failed');
        }
        
        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.playlist-manager');
    }
}
PHP;
    }

    private function generateLivewireViewCode(): string
    {
        return <<<'HTML'
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Spotify Playlists</h1>
        <p class="text-gray-600">Manage your Spotify playlists</p>
    </div>

    <div class="mb-6">
        <div class="relative">
            <input
                type="text"
                wire:model.live="search"
                wire:keydown.enter="searchPlaylists"
                placeholder="Search playlists..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <button
                wire:click="searchPlaylists"
                class="absolute right-2 top-2 px-4 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>Search</span>
                <span wire:loading>Searching...</span>
            </button>
        </div>
    </div>

    @if($loading)
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div>
            <p class="mt-2 text-gray-600">Loading playlists...</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($playlists as $playlist)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    @if($playlist['images'])
                        <img
                            src="{{ $playlist['images'][0]['url'] }}"
                            alt="{{ $playlist['name'] }}"
                            class="w-full h-48 object-cover"
                        />
                    @endif
                    <div class="p-4">
                        <h3 class="font-semibold text-lg">{{ $playlist['name'] }}</h3>
                        <p class="text-gray-600 text-sm">{{ $playlist['tracks']['total'] }} tracks</p>
                        <p class="text-gray-500 text-xs">by {{ $playlist['owner']['display_name'] }}</p>
                        
                        <a
                            href="{{ $playlist['external_urls']['spotify'] }}"
                            target="_blank"
                            class="mt-3 inline-block px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm"
                        >
                            Open in Spotify
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-500">No playlists found</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
HTML;
    }

    private function generateInertiaDashboardMethod(): string
    {
        return <<<'PHP'
    public function dashboard()
    {
        try {
            $userProfile = $this->spotify->users()->getCurrentUser();
            $recentTracks = $this->spotify->users()->getRecentlyPlayed();
            $topArtists = $this->spotify->users()->getTopArtists();
            
            return inertia('Spotify/Dashboard', [
                'user' => $userProfile->json(),
                'recentTracks' => $recentTracks->json(),
                'topArtists' => $topArtists->json(),
            ]);
        } catch (\Exception $e) {
            return inertia('Spotify/Dashboard', [
                'error' => 'Failed to load Spotify data',
            ]);
        }
    }
PHP;
    }

    private function generateInertiaPlaylistsMethod(): string
    {
        return <<<'PHP'
    public function playlists()
    {
        try {
            $playlists = $this->spotify->playlists()->getAll();
            
            return inertia('Spotify/Playlists', [
                'playlists' => $playlists->json(),
            ]);
        } catch (\Exception $e) {
            return inertia('Spotify/Playlists', [
                'error' => 'Failed to load playlists',
            ]);
        }
    }
PHP;
    }
}