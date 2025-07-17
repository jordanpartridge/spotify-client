<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Resources;

use Jordanpartridge\SpotifyClient\Requests\Playlists\AddItemsToPlaylistRequest;
use Jordanpartridge\SpotifyClient\Requests\Playlists\CreatePlaylistRequest;
use Jordanpartridge\SpotifyClient\Requests\Playlists\GetCurrentUserPlaylistsRequest;
use Jordanpartridge\SpotifyClient\Requests\Playlists\GetPlaylistItemsRequest;
use Jordanpartridge\SpotifyClient\Requests\Playlists\GetPlaylistRequest;
use Jordanpartridge\SpotifyClient\Requests\Playlists\GetUserPlaylistsRequest;
use Jordanpartridge\SpotifyClient\Requests\Playlists\RemoveItemsFromPlaylistRequest;
use Jordanpartridge\SpotifyClient\Requests\Playlists\UpdatePlaylistRequest;
use Saloon\Http\Response;

class PlaylistsResource extends BaseResource
{
    public function get(string $id): Response
    {
        return $this->connector->send(new GetPlaylistRequest($id));
    }

    public function getItems(
        string $playlistId,
        ?string $fields = null,
        int $limit = 20,
        int $offset = 0,
        ?string $market = null,
        ?string $additionalTypes = null
    ): Response {
        return $this->connector->send(new GetPlaylistItemsRequest(
            $playlistId,
            $fields,
            $limit,
            $offset,
            $market,
            $additionalTypes
        ));
    }

    public function create(
        string $userId,
        string $name,
        ?string $description = null,
        bool $public = true,
        bool $collaborative = false
    ): Response {
        return $this->connector->send(new CreatePlaylistRequest(
            $userId,
            $name,
            $description,
            $public,
            $collaborative
        ));
    }

    public function addItems(string $playlistId, array $uris, ?int $position = null): Response
    {
        return $this->connector->send(new AddItemsToPlaylistRequest($playlistId, $uris, $position));
    }

    public function removeItems(string $playlistId, array $tracks, ?string $snapshotId = null): Response
    {
        return $this->connector->send(new RemoveItemsFromPlaylistRequest($playlistId, $tracks, $snapshotId));
    }

    public function update(
        string $playlistId,
        ?string $name = null,
        ?string $description = null,
        ?bool $public = null,
        ?bool $collaborative = null
    ): Response {
        return $this->connector->send(new UpdatePlaylistRequest(
            $playlistId,
            $name,
            $description,
            $public,
            $collaborative
        ));
    }

    public function getCurrentUserPlaylists(int $limit = 20, int $offset = 0): Response
    {
        return $this->connector->send(new GetCurrentUserPlaylistsRequest($limit, $offset));
    }

    public function getUserPlaylists(string $userId, int $limit = 20, int $offset = 0): Response
    {
        return $this->connector->send(new GetUserPlaylistsRequest($userId, $limit, $offset));
    }
}
