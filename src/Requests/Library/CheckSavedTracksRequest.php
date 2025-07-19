<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Library;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class CheckSavedTracksRequest extends BaseRequest
{
    public function __construct(
        private readonly array $trackIds
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/tracks/contains';
    }

    protected function defaultQuery(): array
    {
        return [
            'ids' => implode(',', $this->trackIds),
        ];
    }
}
