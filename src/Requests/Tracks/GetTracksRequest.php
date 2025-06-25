<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Tracks;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetTracksRequest extends BaseRequest
{
    public function __construct(
        private readonly array $ids
    ) {}

    public function resolveEndpoint(): string
    {
        return '/tracks';
    }

    protected function defaultQuery(): array
    {
        return [
            'ids' => implode(',', $this->ids),
        ];
    }
}