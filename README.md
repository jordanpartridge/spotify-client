# 🎵 Spotify Client for Laravel

*Well, I've been writing PHP code all night long*  
*Building APIs that just feel so right*  
*Got my Saloon connector, Laravel's my home*  
*This package's got style, won't leave you alone*

## What This Package Does

This Laravel package provides a modern, elegant interface to the Spotify Web API. Built with Saloon HTTP client and following Laravel conventions, it's designed to make working with Spotify's API as smooth as a steel guitar solo.

### Features That'll Make You Holler

- 🎸 **Clean Architecture** - Organized like a well-tuned band
- 🎤 **Saloon HTTP Client** - Smooth as honey, strong as whiskey
- 🎺 **Laravel Integration** - Fits right into your Laravel app like boots in stirrups
- 🎻 **Type Safety** - Contracts and interfaces tighter than new jeans
- 🪕 **OAuth Support** - Authentication flows that just work
- 🎼 **Resource Organization** - Albums, Artists, Tracks, and more - all organized neat

## Installation

```bash
composer require jordanpartridge/spotify-client
```

Then run our fancy install command that'll set you up proper:

```bash
php artisan spotify:install
# or use the setup alias
php artisan spotify:setup
```

*That command's gonna open your browser, help you register your app*  
*Get your tokens sorted, close that authentication gap*  
*From Client ID to secrets, we'll handle it all*  
*This setup's so easy, you'll have a ball*

## Basic Usage

```php
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

// Inject the client (Laravel's DI is sweeter than tea)
public function __construct(SpotifyClientInterface $spotify)
{
    $this->spotify = $spotify;
}

// Get an album (data comes back clean as mountain air)
$album = $this->spotify->albums()->get('album-id');

// Search for artists (find 'em faster than a hound dog)
$artists = $this->spotify->artists()->getMultiple(['artist1', 'artist2']);

// Get track details (metadata richer than bottom land)
$track = $this->spotify->tracks()->get('track-id');
```

## Architecture That Sings

This package follows a pattern cleaner than Sunday clothes:

### The Main Players

- **SpotifyConnector** - The bandleader, handles the base URL and keeps everyone in time
- **SpotifyClient** - Your main interface, implements the contract like a handshake deal  
- **Resources** - Organized by what they do (Albums, Artists, Tracks, etc.)
- **Requests** - Individual API calls, each one knows its job
- **Contracts** - Interfaces that keep everything honest

### Directory Structure

```
src/
├── Contracts/           # Promises we keep
├── Resources/           # Organized by purpose
├── Requests/            # Individual API workers
├── SpotifyConnector.php # The main connection
└── SpotifyClient.php    # Your friendly interface
```

*We built this thing with Saloon for the HTTP*  
*Laravel Package Tools for the setup flow*  
*Every class has its place, every method has its role*  
*This architecture's solid, right down to the soul*

## Authentication Flows

### Client Credentials (App-Only)
Perfect for when you just need public data:

```php
// Configure in your .env
SPOTIFY_CLIENT_ID=your_client_id
SPOTIFY_CLIENT_SECRET=your_client_secret
SPOTIFY_AUTH_FLOW=client_credentials
```

### Authorization Code (User Context)
When you need user permissions:

```php
// The install command will help you set this up
SPOTIFY_AUTH_FLOW=authorization_code
SPOTIFY_REDIRECT_URI=http://localhost:8000/callback
```

## Configuration

The config file's got everything you need:

```php
// config/spotify-client.php
return [
    'client_id' => env('SPOTIFY_CLIENT_ID'),
    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
    'default_market' => env('SPOTIFY_DEFAULT_MARKET', 'US'),
    // ... and much more
];
```

## Contributing

Pull requests welcome! This package follows:

- PSR-12 coding standards (Laravel Pint keeps us honest)
- PHPStan static analysis (level 8, strict as a preacher)
- Pest PHP for testing (when we get them tests written)

## Requirements

- PHP 8.2 or higher (modern as a Tesla truck)
- Laravel 10.0 or higher (staying current, staying strong)

## License

MIT License - free as a bird, open as the range.

---

*This package was built with love, Laravel, and a appreciation for good architecture*  
*May your API calls be swift and your tokens never expire* 🤠

## Credits

Built by [Jordan Partridge](https://github.com/jordanpartridge) with inspiration from:
- The Spotify Web API team
- The Saloon PHP community  
- The Laravel ecosystem
- Good music and clean code