<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient;

use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;
use Jordanpartridge\SpotifyClient\Resources\AlbumsResource;
use Jordanpartridge\SpotifyClient\Resources\ArtistsResource;
use Jordanpartridge\SpotifyClient\Resources\PlaylistsResource;
use Jordanpartridge\SpotifyClient\Resources\TracksResource;
use Jordanpartridge\SpotifyClient\Resources\UsersResource;

class SpotifyClient implements SpotifyClientInterface
{
    public function __construct(
        private readonly SpotifyConnector $connector
    ) {}

    public function albums(): AlbumsResource
    {
        return $this->connector->albums();
    }

    public function artists(): ArtistsResource
    {
        return $this->connector->artists();
    }

    public function playlists(): PlaylistsResource
    {
        return $this->connector->playlists();
    }

    public function tracks(): TracksResource
    {
        return $this->connector->tracks();
    }

    public function users(): UsersResource
    {
        return $this->connector->users();
    }
}