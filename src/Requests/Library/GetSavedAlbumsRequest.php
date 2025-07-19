<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Library;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetSavedAlbumsRequest extends BaseRequest
{
    public function __construct(
        private readonly int $limit = 20,
        private readonly int $offset = 0,
        private readonly ?string $market = null
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/albums';
    }

    protected function defaultQuery(): array
    {
        $query = [
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];

        if ($this->market) {
            $query['market'] = $this->market;
        }

        return $query;
    }
}
