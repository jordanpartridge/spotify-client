<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Player;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class PlayRequest extends BaseRequest
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly ?array $uris = null,
        private readonly ?string $contextUri = null,
        private readonly ?int $positionMs = null,
        private readonly ?string $deviceId = null
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/player/play';
    }

    protected function defaultQuery(): array
    {
        $query = [];

        if ($this->deviceId) {
            $query['device_id'] = $this->deviceId;
        }

        return $query;
    }

    protected function defaultBody(): array
    {
        $body = [];

        if ($this->uris) {
            $body['uris'] = $this->uris;
        }

        if ($this->contextUri) {
            $body['context_uri'] = $this->contextUri;
        }

        if ($this->positionMs !== null) {
            $body['position_ms'] = $this->positionMs;
        }

        return $body;
    }
}