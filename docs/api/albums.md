# Albums API Documentation

## 🎵 Albums Resource

Access Spotify's vast album catalog with full metadata and track listings.

## Available Methods

### Get Single Album
Retrieve detailed information about a specific album.

```php
$album = $client->albums()->get('4aawyAB9vmqN3uQ7FjRGTy');
```

**Request Details:**
- **Endpoint**: `GET /albums/{id}`
- **Auth**: App-level or User-level
- **Rate Limit**: Standard

**Response Structure:**
```json
{
  "id": "4aawyAB9vmqN3uQ7FjRGTy",
  "name": "Global Warming",
  "artists": [
    {
      "id": "6XyY86QOPPrYVGvF9ch6wz",
      "name": "Pitbull"
    }
  ],
  "album_type": "album",
  "total_tracks": 16,
  "available_markets": ["AD", "AE", "AG"],
  "external_urls": {
    "spotify": "https://open.spotify.com/album/4aawyAB9vmqN3uQ7FjRGTy"
  },
  "href": "https://api.spotify.com/v1/albums/4aawyAB9vmqN3uQ7FjRGTy",
  "images": [
    {
      "url": "https://i.scdn.co/image/ab67616d0000b273...",
      "height": 640,
      "width": 640
    }
  ],
  "release_date": "2012-11-19",
  "release_date_precision": "day",
  "tracks": {
    "href": "https://api.spotify.com/v1/albums/4aawyAB9vmqN3uQ7FjRGTy/tracks",
    "limit": 50,
    "next": null,
    "offset": 0,
    "previous": null,
    "total": 16,
    "items": [...]
  },
  "type": "album",
  "uri": "spotify:album:4aawyAB9vmqN3uQ7FjRGTy"
}
```

### Get Multiple Albums
Retrieve information for multiple albums in a single request.

```php
$albums = $client->albums()->getMultiple([
    '4aawyAB9vmqN3uQ7FjRGTy',
    '1A2GTWGtFfWp7KSQTwWOyo',
    '2noRn2Aes5aoNVsU6iWThc'
]);
```

**Request Details:**
- **Endpoint**: `GET /albums?ids={ids}`
- **Auth**: App-level or User-level
- **Rate Limit**: Standard
- **Limits**: Maximum 20 album IDs per request

**Query Parameters:**
- `ids` (required): Comma-separated list of album IDs
- `market` (optional): ISO 3166-1 alpha-2 country code

**Response Structure:**
```json
{
  "albums": [
    {
      "id": "4aawyAB9vmqN3uQ7FjRGTy",
      "name": "Global Warming",
      // ... full album object
    },
    {
      "id": "1A2GTWGtFfWp7KSQTwWOyo", 
      "name": "An Awesome Wave",
      // ... full album object
    }
  ]
}
```

## Implementation Details

### GetAlbumRequest Class
```php
namespace Jordanpartridge\SpotifyClient\Requests\Albums;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetAlbumRequest extends BaseRequest
{
    public function __construct(
        private readonly string $id
    ) {}

    public function resolveEndpoint(): string
    {
        return "/albums/{$this->id}";
    }
}
```

### GetAlbumsRequest Class  
```php
namespace Jordanpartridge\SpotifyClient\Requests\Albums;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetAlbumsRequest extends BaseRequest
{
    public function __construct(
        private readonly array $ids
    ) {}

    public function resolveEndpoint(): string
    {
        return '/albums';
    }

    protected function defaultQuery(): array
    {
        return [
            'ids' => implode(',', $this->ids),
        ];
    }
}
```

### AlbumsResource Class
```php
namespace Jordanpartridge\SpotifyClient\Resources;

class AlbumsResource extends BaseResource
{
    public function get(string $id): \Saloon\Http\Response
    {
        return $this->connector->send(new GetAlbumRequest($id));
    }

    public function getMultiple(array $ids): \Saloon\Http\Response
    {
        if (count($ids) > 20) {
            throw new \InvalidArgumentException('Maximum 20 album IDs allowed per request');
        }

        return $this->connector->send(new GetAlbumsRequest($ids));
    }
}
```

## Usage Examples

### Basic Album Information
```php
use Jordanpartridge\SpotifyClient\SpotifyClient;

$client = app(SpotifyClient::class);

// Get a single album
$response = $client->albums()->get('4aawyAB9vmqN3uQ7FjRGTy');
$album = $response->json();

echo "Album: {$album['name']} by {$album['artists'][0]['name']}\n";
echo "Released: {$album['release_date']}\n";
echo "Tracks: {$album['total_tracks']}\n";
```

### Batch Album Lookup
```php
$albumIds = [
    '4aawyAB9vmqN3uQ7FjRGTy',  // Pitbull - Global Warming
    '1A2GTWGtFfWp7KSQTwWOyo',  // alt-J - An Awesome Wave
    '2noRn2Aes5aoNVsU6iWThc'   // Daft Punk - Random Access Memories
];

$response = $client->albums()->getMultiple($albumIds);
$albums = $response->json()['albums'];

foreach ($albums as $album) {
    if ($album !== null) { // Album might be null if not available
        echo "{$album['name']} ({$album['release_date']})\n";
    }
}
```

### Album Track Listing
```php
$response = $client->albums()->get('4aawyAB9vmqN3uQ7FjRGTy');
$album = $response->json();

echo "Tracks on {$album['name']}:\n";
foreach ($album['tracks']['items'] as $track) {
    $duration = gmdate('i:s', $track['duration_ms'] / 1000);
    echo "  {$track['track_number']}. {$track['name']} ({$duration})\n";
}
```

### Market-Specific Requests
```php
// Add market parameter for region-specific availability
$request = new GetAlbumRequest('4aawyAB9vmqN3uQ7FjRGTy');
$request->query()->add('market', 'US');

$response = $client->connector->send($request);
$album = $response->json();

// Check if album is available in the specified market
$isAvailable = in_array('US', $album['available_markets']);
```

## Error Handling

### Common Errors
```php
use Saloon\Exceptions\Request\RequestException;

try {
    $album = $client->albums()->get('invalid-album-id');
} catch (RequestException $e) {
    match ($e->getCode()) {
        400 => throw new \Exception('Invalid album ID format'),
        404 => throw new \Exception('Album not found'),
        429 => throw new \Exception('Rate limit exceeded'),
        default => throw new \Exception("API error: {$e->getMessage()}")
    };
}
```

### Validation
```php
// Validate album ID format (22 characters, alphanumeric)
if (!preg_match('/^[a-zA-Z0-9]{22}$/', $albumId)) {
    throw new \InvalidArgumentException('Invalid Spotify album ID format');
}

// Validate batch size
if (count($albumIds) > 20) {
    throw new \InvalidArgumentException('Maximum 20 albums per request');
}
```

## Response Caching

### Laravel Cache Integration
```php
use Illuminate\Support\Facades\Cache;

public function getCachedAlbum(string $albumId): array
{
    return Cache::remember(
        key: "spotify.album.{$albumId}",
        ttl: now()->addHours(6), // Albums rarely change
        callback: fn() => $this->client->albums()->get($albumId)->json()
    );
}
```

### Cache Tags for Invalidation
```php
// Tag cache entries for easier invalidation
Cache::tags(['spotify', 'albums'])->remember(
    "album.{$albumId}",
    3600,
    fn() => $this->client->albums()->get($albumId)->json()
);

// Clear all album cache
Cache::tags(['albums'])->flush();
```

## Testing

### Mock Responses
```php
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

public function test_get_album()
{
    $mockClient = new MockClient([
        MockResponse::make([
            'id' => '4aawyAB9vmqN3uQ7FjRGTy',
            'name' => 'Global Warming',
            'artists' => [['id' => '6XyY86QOPPrYVGvF9ch6wz', 'name' => 'Pitbull']],
            'total_tracks' => 16
        ])
    ]);

    $this->connector->withMockClient($mockClient);
    
    $response = $this->client->albums()->get('4aawyAB9vmqN3uQ7FjRGTy');
    $album = $response->json();
    
    $this->assertEquals('Global Warming', $album['name']);
}
```

## Rate Limiting

Albums API follows Spotify's standard rate limits:
- **Standard**: ~100 requests per minute per application
- **Burst**: Short bursts of higher traffic allowed
- **Headers**: `X-RateLimit-Remaining`, `Retry-After`

```php
try {
    $response = $client->albums()->get($albumId);
} catch (RequestException $e) {
    if ($e->getCode() === 429) {
        $retryAfter = $e->getResponse()->getHeader('Retry-After')[0] ?? 60;
        sleep($retryAfter);
        // Retry request
    }
}
```

---

**Related**: [Artists API](artists.md) | [Tracks API](tracks.md) | [Error Handling](../advanced/error-handling.md)