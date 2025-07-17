<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Contracts;

use Jordanpartridge\SpotifyClient\Resources\AlbumsResource;
use Jordanpartridge\SpotifyClient\Resources\ArtistsResource;
use Jordanpartridge\SpotifyClient\Resources\PlayerResource;
use Jordanpartridge\SpotifyClient\Resources\PlaylistsResource;
use Jordanpartridge\SpotifyClient\Resources\SearchResource;
use Jordanpartridge\SpotifyClient\Resources\TracksResource;
use Jordanpartridge\SpotifyClient\Resources\UsersResource;

interface SpotifyClientInterface
{
    public function albums(): AlbumsResource;

    public function artists(): ArtistsResource;

    public function playlists(): PlaylistsResource;

    public function tracks(): TracksResource;

    public function users(): UsersResource;

    public function player(): PlayerResource;

    public function search(): SearchResource;
}
