<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Playlists;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetCurrentUserPlaylistsRequest extends BaseRequest
{
    public function __construct(
        private readonly int $limit = 20,
        private readonly int $offset = 0
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/playlists';
    }

    protected function defaultQuery(): array
    {
        return [
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];
    }
}
