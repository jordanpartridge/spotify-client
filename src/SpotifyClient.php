<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient;

use Jordanpartridge\SpotifyClient\Contracts\AuthenticatorInterface;
use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;
use Jordanpartridge\SpotifyClient\Resources\AlbumsResource;
use Jordanpartridge\SpotifyClient\Resources\ArtistsResource;
use Jordanpartridge\SpotifyClient\Resources\DevicesResource;
use Jordanpartridge\SpotifyClient\Resources\LibraryResource;
use Jordanpartridge\SpotifyClient\Resources\PlayerResource;
use Jordanpartridge\SpotifyClient\Resources\PlaylistsResource;
use Jordanpartridge\SpotifyClient\Resources\SearchResource;
use Jordanpartridge\SpotifyClient\Resources\TracksResource;
use Jordanpartridge\SpotifyClient\Resources\UsersResource;

class SpotifyClient implements SpotifyClientInterface
{
    public function __construct(
        private readonly SpotifyConnector $connector,
        ?AuthenticatorInterface $authenticator = null
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

    public function player(): PlayerResource
    {
        return $this->connector->player();
    }

    public function search(): SearchResource
    {
        return $this->connector->search();
    }

    public function devices(): DevicesResource
    {
        return $this->connector->devices();
    }

    public function library(): LibraryResource
    {
        return $this->connector->library();
    }
}
