<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Resources;

use Jordanpartridge\SpotifyClient\Requests\Tracks\GetTrackRequest;
use Jordanpartridge\SpotifyClient\Requests\Tracks\GetTracksRequest;
use Saloon\Http\Response;

class TracksResource extends BaseResource
{
    public function get(string $id): Response
    {
        return $this->connector->send(new GetTrackRequest($id));
    }

    public function getMultiple(array $ids): Response
    {
        return $this->connector->send(new GetTracksRequest($ids));
    }
}