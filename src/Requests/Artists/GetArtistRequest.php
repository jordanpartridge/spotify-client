<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Artists;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetArtistRequest extends BaseRequest
{
    public function __construct(
        private readonly string $id
    ) {}

    public function resolveEndpoint(): string
    {
        return "/artists/{$this->id}";
    }
}
