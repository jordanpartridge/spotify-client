<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Search;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class SearchRequest extends BaseRequest
{
    protected Method $method = Method::GET;

    /**
     * Not $query: Saloon\Http\Request already owns a $query property (the
     * query-string store), and redeclaring it with another type is fatal.
     */
    private readonly string $searchQuery;

    public function __construct(
        string $query,
        private readonly array $types,
        private readonly ?string $market = null,
        private readonly int $limit = 20,
        private readonly int $offset = 0,
        private readonly bool $includeExternal = false
    ) {
        $this->searchQuery = $query;
    }

    public function resolveEndpoint(): string
    {
        return '/search';
    }

    protected function defaultQuery(): array
    {
        $query = [
            'q' => $this->searchQuery,
            'type' => implode(',', $this->types),
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];

        if ($this->market) {
            $query['market'] = $this->market;
        }

        if ($this->includeExternal) {
            $query['include_external'] = 'audio';
        }

        return $query;
    }
}
