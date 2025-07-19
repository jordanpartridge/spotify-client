<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Facades;

use Illuminate\Support\Facades\Facade;
use Jordanpartridge\SpotifyClient\Resources\AlbumsResource;
use Jordanpartridge\SpotifyClient\Resources\ArtistsResource;
use Jordanpartridge\SpotifyClient\Resources\DevicesResource;
use Jordanpartridge\SpotifyClient\Resources\LibraryResource;
use Jordanpartridge\SpotifyClient\Resources\PlayerResource;
use Jordanpartridge\SpotifyClient\Resources\PlaylistsResource;
use Jordanpartridge\SpotifyClient\Resources\SearchResource;
use Jordanpartridge\SpotifyClient\Resources\TracksResource;
use Jordanpartridge\SpotifyClient\Resources\UsersResource;

/**
 * Spotify Facade - Making music with Laravel elegance! 🎵
 *
 * @method static AlbumsResource albums()
 * @method static ArtistsResource artists()
 * @method static DevicesResource devices()
 * @method static LibraryResource library()
 * @method static PlayerResource player()
 * @method static PlaylistsResource playlists()
 * @method static SearchResource search()
 * @method static TracksResource tracks()
 * @method static UsersResource users()
 *
 * @see \Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface
 */
class SpotifyFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'spotify-client';
    }
}
