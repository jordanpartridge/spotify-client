<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Resources;

use Jordanpartridge\SpotifyClient\Requests\Artists\GetArtistRequest;
use Jordanpartridge\SpotifyClient\Requests\Artists\GetArtistsRequest;
use Saloon\Http\Response;

class ArtistsResource extends BaseResource
{
    public function get(string $id): Response
    {
        return $this->connector->send(new GetArtistRequest($id));
    }

    public function getMultiple(array $ids): Response
    {
        return $this->connector->send(new GetArtistsRequest($ids));
    }
}
