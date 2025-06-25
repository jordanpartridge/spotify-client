<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Resources;

use Jordanpartridge\SpotifyClient\Requests\Albums\GetAlbumRequest;
use Jordanpartridge\SpotifyClient\Requests\Albums\GetAlbumsRequest;
use Saloon\Http\Response;

class AlbumsResource extends BaseResource
{
    public function get(string $id): Response
    {
        return $this->connector->send(new GetAlbumRequest($id));
    }

    public function getMultiple(array $ids): Response
    {
        return $this->connector->send(new GetAlbumsRequest($ids));
    }
}