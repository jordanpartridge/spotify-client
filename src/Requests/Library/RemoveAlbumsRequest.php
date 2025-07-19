<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Library;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class RemoveAlbumsRequest extends BaseRequest
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly array $albumIds
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/albums';
    }

    protected function defaultQuery(): array
    {
        return [
            'ids' => implode(',', $this->albumIds),
        ];
    }
}
