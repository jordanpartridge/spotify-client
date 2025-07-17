<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Tracks;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetTrackRequest extends BaseRequest
{
    public function __construct(
        private readonly string $id
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tracks/{$this->id}";
    }
}
