<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Library;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class SaveTracksRequest extends BaseRequest
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly array $trackIds
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/tracks';
    }

    protected function defaultQuery(): array
    {
        return [
            'ids' => implode(',', $this->trackIds),
        ];
    }
}
