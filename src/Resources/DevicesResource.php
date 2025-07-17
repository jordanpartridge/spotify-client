<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Resources;

use Jordanpartridge\SpotifyClient\Requests\Devices\GetAvailableDevicesRequest;
use Jordanpartridge\SpotifyClient\Requests\Devices\TransferPlaybackRequest;
use Saloon\Http\Response;

class DevicesResource extends BaseResource
{
    public function getAvailable(): Response
    {
        return $this->connector->send(new GetAvailableDevicesRequest);
    }

    public function transferPlayback(array $deviceIds, bool $play = false): Response
    {
        return $this->connector->send(new TransferPlaybackRequest($deviceIds, $play));
    }
}
