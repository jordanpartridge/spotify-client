<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Search;

class SearchTracksRequest extends SearchRequest
{
    public function __construct(
        string $query,
        ?string $market = null,
        int $limit = 20,
        int $offset = 0,
        bool $includeExternal = false
    ) {
        parent::__construct(
            query: $query,
            types: ['track'],
            market: $market,
            limit: $limit,
            offset: $offset,
            includeExternal: $includeExternal
        );
    }
}
