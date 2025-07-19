<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Library;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class CheckFollowingRequest extends BaseRequest
{
    public function __construct(
        private readonly string $type, // 'artist' or 'user'
        private readonly array $ids
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/following/contains';
    }

    protected function defaultQuery(): array
    {
        return [
            'type' => $this->type,
            'ids' => implode(',', $this->ids),
        ];
    }
}
