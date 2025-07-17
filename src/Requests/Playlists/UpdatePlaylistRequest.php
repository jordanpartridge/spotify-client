<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Playlists;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class UpdatePlaylistRequest extends BaseRequest
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly string $playlistId,
        private readonly ?string $name = null,
        private readonly ?string $description = null,
        private readonly ?bool $public = null,
        private readonly ?bool $collaborative = null
    ) {}

    public function resolveEndpoint(): string
    {
        return "/playlists/{$this->playlistId}";
    }

    protected function defaultBody(): array
    {
        $body = [];

        if ($this->name !== null) {
            $body['name'] = $this->name;
        }

        if ($this->description !== null) {
            $body['description'] = $this->description;
        }

        if ($this->public !== null) {
            $body['public'] = $this->public;
        }

        if ($this->collaborative !== null) {
            $body['collaborative'] = $this->collaborative;
        }

        return $body;
    }
}
