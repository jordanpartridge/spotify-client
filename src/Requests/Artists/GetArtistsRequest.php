<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Artists;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetArtistsRequest extends BaseRequest
{
    public function __construct(
        private readonly array $ids
    ) {}

    public function resolveEndpoint(): string
    {
        return '/artists';
    }

    protected function defaultQuery(): array
    {
        return [
            'ids' => implode(',', $this->ids),
        ];
    }
}
