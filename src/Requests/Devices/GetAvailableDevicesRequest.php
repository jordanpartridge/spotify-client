<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Devices;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class GetAvailableDevicesRequest extends BaseRequest
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/me/player/devices';
    }
}
