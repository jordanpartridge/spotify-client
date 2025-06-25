<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Albums;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetAlbumsRequest extends BaseRequest
{
    public function __construct(
        private readonly array $ids
    ) {}

    public function resolveEndpoint(): string
    {
        return '/albums';
    }

    protected function defaultQuery(): array
    {
        return [
            'ids' => implode(',', $this->ids),
        ];
    }
}