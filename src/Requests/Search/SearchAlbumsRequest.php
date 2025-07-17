<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Search;

class SearchAlbumsRequest extends SearchRequest
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
            types: ['album'],
            market: $market,
            limit: $limit,
            offset: $offset,
            includeExternal: $includeExternal
        );
    }
}
