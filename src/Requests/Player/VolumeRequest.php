<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Player;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class VolumeRequest extends BaseRequest
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $volumePercent,
        private readonly ?string $deviceId = null
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/player/volume';
    }

    protected function defaultQuery(): array
    {
        $query = ['volume_percent' => $this->volumePercent];

        if ($this->deviceId) {
            $query['device_id'] = $this->deviceId;
        }

        return $query;
    }
}
