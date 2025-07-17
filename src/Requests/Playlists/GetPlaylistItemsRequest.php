<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Playlists;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetPlaylistItemsRequest extends BaseRequest
{
    public function __construct(
        private readonly string $playlistId,
        private readonly ?string $fields = null,
        private readonly int $limit = 20,
        private readonly int $offset = 0,
        private readonly ?string $market = null,
        private readonly ?string $additionalTypes = null
    ) {}

    public function resolveEndpoint(): string
    {
        return "/playlists/{$this->playlistId}/tracks";
    }

    protected function defaultQuery(): array
    {
        $query = [
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];

        if ($this->fields !== null) {
            $query['fields'] = $this->fields;
        }

        if ($this->market !== null) {
            $query['market'] = $this->market;
        }

        if ($this->additionalTypes !== null) {
            $query['additional_types'] = $this->additionalTypes;
        }

        return $query;
    }
}
