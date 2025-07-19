<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Library;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetFollowedArtistsRequest extends BaseRequest
{
    public function __construct(
        private readonly int $limit = 20,
        private readonly ?string $after = null
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/following';
    }

    protected function defaultQuery(): array
    {
        $query = [
            'type' => 'artist',
            'limit' => $this->limit,
        ];

        if ($this->after) {
            $query['after'] = $this->after;
        }

        return $query;
    }
}
