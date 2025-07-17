<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Playlists;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class CreatePlaylistRequest extends BaseRequest
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $userId,
        private readonly string $name,
        private readonly ?string $description = null,
        private readonly bool $public = true,
        private readonly bool $collaborative = false
    ) {}

    public function resolveEndpoint(): string
    {
        return "/users/{$this->userId}/playlists";
    }

    protected function defaultBody(): array
    {
        $body = [
            'name' => $this->name,
            'public' => $this->public,
            'collaborative' => $this->collaborative,
        ];

        if ($this->description !== null) {
            $body['description'] = $this->description;
        }

        return $body;
    }
}
