<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Player;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class NextRequest extends BaseRequest
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly ?string $deviceId = null
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/player/next';
    }

    protected function defaultQuery(): array
    {
        $query = [];

        if ($this->deviceId) {
            $query['device_id'] = $this->deviceId;
        }

        return $query;
    }
}
