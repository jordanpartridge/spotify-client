# Spotify Facade Integration

The Spotify client package provides elegant Laravel Facade integration for seamless API access with clean, intuitive syntax. 🎵

## Quick Start

```php
use Spotify;

// Search for tracks with beautiful facade syntax
$tracks = Spotify::search()->tracks('The Beatles', 10);

// Save tracks to your library
Spotify::library()->saveTrack($trackId);

// Control playback
Spotify::player()->play();
```

## Installation

The facade is automatically registered when you install the package. No additional setup required!

```bash
composer require jordanpartridge/spotify-client
```

The `Spotify` facade alias is automatically available in your Laravel application.

## Before vs After

### Before (Dependency Injection)
```php
class MusicController extends Controller
{
    public function __construct(
        private SpotifyClientInterface $spotify
    ) {}

    public function search(Request $request)
    {
        $results = $this->spotify->search()->tracks($request->query);
        return response()->json($results->json());
    }

    public function saveTrack(string $trackId)
    {
        $this->spotify->library()->saveTrack($trackId);
        return response()->json(['success' => true]);
    }
}
```

### After (Elegant Facade)
```php
class MusicController extends Controller
{
    public function search(Request $request)
    {
        $results = Spotify::search()->tracks($request->query);
        return response()->json($results->json());
    }

    public function saveTrack(string $trackId)
    {
        Spotify::library()->saveTrack($trackId);
        return response()->json(['success' => true]);
    }
}
```

## Complete API Access

All Spotify resources are available through the facade:

### Search & Discovery
```php
// Search for content
$tracks = Spotify::search()->tracks('Sweet Caroline');
$albums = Spotify::search()->albums('Abbey Road');
$artists = Spotify::search()->artists('The Beatles');

// Get specific items
$track = Spotify::tracks()->get($trackId);
$album = Spotify::albums()->get($albumId);
$artist = Spotify::artists()->get($artistId);
```

### Library Management
```php
// Save content to library
Spotify::library()->saveTrack($trackId);
Spotify::library()->saveAlbums([$albumId1, $albumId2]);

// Check what's saved
$isSaved = Spotify::library()->isTrackSaved($trackId);
$savedTracks = Spotify::library()->getSavedTracks();

// Follow artists
Spotify::library()->followArtist($artistId);
$followedArtists = Spotify::library()->getFollowedArtists();
```

### Playback Control
```php
// Control playback
Spotify::player()->play();
Spotify::player()->pause();
Spotify::player()->next();
Spotify::player()->previous();

// Set volume
Spotify::player()->volume(50);

// Get current state
$currentTrack = Spotify::player()->currentlyPlaying();
```

### Playlist Management
```php
// Get playlists
$playlists = Spotify::playlists()->getCurrentUserPlaylists();
$playlist = Spotify::playlists()->get($playlistId);

// Create and modify playlists
$newPlaylist = Spotify::playlists()->create($userId, 'My Awesome Playlist');
Spotify::playlists()->addItems($playlistId, [$trackUri1, $trackUri2]);
```

### Device Management
```php
// Get available devices
$devices = Spotify::devices()->getAvailable();

// Transfer playback
Spotify::devices()->transfer($deviceId);
```

### User Information
```php
// Get user profile
$profile = Spotify::users()->me();
$user = Spotify::users()->get($userId);
```

## Real-World Examples

### Music Discovery App
```php
class DiscoveryController extends Controller
{
    public function discover(Request $request)
    {
        $genre = $request->input('genre', 'pop');
        
        // Search for tracks in the genre
        $tracks = Spotify::search()->tracks("genre:{$genre}", 20);
        
        // Get track details
        $trackData = $tracks->json();
        
        return view('discover', [
            'tracks' => $trackData['tracks']['items'],
            'genre' => $genre
        ]);
    }

    public function saveToLibrary(Request $request)
    {
        $trackId = $request->input('track_id');
        
        // Save to user's library with one simple call
        Spotify::library()->saveTrack($trackId);
        
        return response()->json([
            'message' => 'Track saved to your library! 🎵'
        ]);
    }
}
```

### Playlist Manager
```php
class PlaylistController extends Controller
{
    public function createFromSearch(Request $request)
    {
        $query = $request->input('search');
        $playlistName = $request->input('name');
        
        // Search for tracks
        $searchResults = Spotify::search()->tracks($query, 10);
        $tracks = $searchResults->json()['tracks']['items'];
        
        // Get current user
        $user = Spotify::users()->me()->json();
        
        // Create playlist
        $playlist = Spotify::playlists()->create(
            $user['id'], 
            $playlistName,
            "Created from search: {$query}"
        );
        
        // Add tracks to playlist
        $trackUris = array_map(fn($track) => $track['uri'], $tracks);
        Spotify::playlists()->addItems($playlist->json()['id'], $trackUris);
        
        return response()->json([
            'playlist' => $playlist->json(),
            'tracks_added' => count($trackUris)
        ]);
    }
}
```

### Music Analytics
```php
class AnalyticsController extends Controller
{
    public function libraryStats()
    {
        // Get saved tracks
        $savedTracks = Spotify::library()->getSavedTracks(50);
        $tracks = $savedTracks->json()['items'];
        
        // Analyze genres and artists
        $artists = [];
        $genres = [];
        
        foreach ($tracks as $item) {
            $track = $item['track'];
            foreach ($track['artists'] as $artist) {
                $artists[] = $artist['name'];
                
                // Get artist details for genres
                $artistDetails = Spotify::artists()->get($artist['id']);
                $genres = array_merge($genres, $artistDetails->json()['genres']);
            }
        }
        
        return response()->json([
            'total_tracks' => count($tracks),
            'unique_artists' => count(array_unique($artists)),
            'top_genres' => array_count_values($genres),
        ]);
    }
}
```

## Benefits

### ✅ Clean Syntax
No more verbose dependency injection - just clean, readable facade calls.

### ✅ Laravel Native Feel
Feels like any other Laravel facade (`Cache::get()`, `DB::table()`, etc.).

### ✅ IDE Support
Full autocompletion and intellisense support in IDEs like PhpStorm.

### ✅ Reduced Boilerplate
Less constructor injection and property management needed.

### ✅ Consistent API
Same methods and responses as the injection approach, just cleaner syntax.

### ✅ Backward Compatible
Existing dependency injection code continues to work unchanged.

## Configuration

The facade uses the same configuration as the injected client. Set your environment variables:

```env
SPOTIFY_CLIENT_ID=your_client_id
SPOTIFY_CLIENT_SECRET=your_client_secret
SPOTIFY_AUTH_FLOW=client_credentials
```

## Advanced Usage

### Custom Service Resolution
If you need custom configuration per request:

```php
// Still works with dependency injection when needed
class CustomMusicService
{
    public function __construct(
        private SpotifyClientInterface $spotify
    ) {}

    public function processWithCustomAuth()
    {
        // Use injected client for custom auth scenarios
        return $this->spotify->search()->tracks('query');
    }
}

// But use facade for standard operations
class StandardMusicService
{
    public function quickSearch(string $query)
    {
        return Spotify::search()->tracks($query);
    }
}
```

## Summary

The Spotify facade provides the perfect balance of elegance and functionality:

- **Simple**: `Spotify::search()->tracks('query')`
- **Powerful**: Full API access with all resources
- **Laravel Native**: Feels like built-in Laravel features
- **Backward Compatible**: Existing code continues to work

🎵 **Make beautiful music with beautiful code!** 🎶