<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Playlists;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class RemoveItemsFromPlaylistRequest extends BaseRequest
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly string $playlistId,
        private readonly array $tracks,
        private readonly ?string $snapshotId = null
    ) {}

    public function resolveEndpoint(): string
    {
        return "/playlists/{$this->playlistId}/tracks";
    }

    protected function defaultBody(): array
    {
        $body = [
            'tracks' => $this->tracks,
        ];

        if ($this->snapshotId !== null) {
            $body['snapshot_id'] = $this->snapshotId;
        }

        return $body;
    }
}
