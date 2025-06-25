<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Albums;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetAlbumRequest extends BaseRequest
{
    public function __construct(
        private readonly string $id
    ) {}

    public function resolveEndpoint(): string
    {
        return "/albums/{$this->id}";
    }
}