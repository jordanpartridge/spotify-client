<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Resources;

use Jordanpartridge\SpotifyClient\Requests\Playlists\GetPlaylistRequest;
use Saloon\Http\Response;

class PlaylistsResource extends BaseResource
{
    public function get(string $id): Response
    {
        return $this->connector->send(new GetPlaylistRequest($id));
    }
}
