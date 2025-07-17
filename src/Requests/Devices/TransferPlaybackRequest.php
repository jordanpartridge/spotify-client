<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Devices;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class TransferPlaybackRequest extends BaseRequest
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly array $deviceIds,
        private readonly bool $play = false
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/player';
    }

    protected function defaultBody(): array
    {
        return [
            'device_ids' => $this->deviceIds,
            'play' => $this->play,
        ];
    }
}
