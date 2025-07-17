<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient;

use Jordanpartridge\SpotifyClient\Resources\AlbumsResource;
use Jordanpartridge\SpotifyClient\Resources\ArtistsResource;
use Jordanpartridge\SpotifyClient\Resources\PlayerResource;
use Jordanpartridge\SpotifyClient\Resources\PlaylistsResource;
use Jordanpartridge\SpotifyClient\Resources\SearchResource;
use Jordanpartridge\SpotifyClient\Resources\TracksResource;
use Jordanpartridge\SpotifyClient\Resources\UsersResource;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class SpotifyConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    public function resolveBaseUrl(): string
    {
        return 'https://api.spotify.com/v1';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function albums(): AlbumsResource
    {
        return new AlbumsResource($this);
    }

    public function artists(): ArtistsResource
    {
        return new ArtistsResource($this);
    }

    public function playlists(): PlaylistsResource
    {
        return new PlaylistsResource($this);
    }

    public function tracks(): TracksResource
    {
        return new TracksResource($this);
    }

    public function users(): UsersResource
    {
        return new UsersResource($this);
    }

    public function player(): PlayerResource
    {
        return new PlayerResource($this);
    }

    public function search(): SearchResource
    {
        return new SearchResource($this);
    }
}
