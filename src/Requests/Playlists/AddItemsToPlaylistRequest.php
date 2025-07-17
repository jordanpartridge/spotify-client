<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Playlists;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class AddItemsToPlaylistRequest extends BaseRequest
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $playlistId,
        private readonly array $uris,
        private readonly ?int $position = null
    ) {}

    public function resolveEndpoint(): string
    {
        return "/playlists/{$this->playlistId}/tracks";
    }

    protected function defaultBody(): array
    {
        $body = [
            'uris' => $this->uris,
        ];

        if ($this->position !== null) {
            $body['position'] = $this->position;
        }

        return $body;
    }
}
