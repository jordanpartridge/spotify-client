<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Library;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class CheckSavedAlbumsRequest extends BaseRequest
{
    public function __construct(
        private readonly array $albumIds
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/albums/contains';
    }

    protected function defaultQuery(): array
    {
        return [
            'ids' => implode(',', $this->albumIds),
        ];
    }
}
