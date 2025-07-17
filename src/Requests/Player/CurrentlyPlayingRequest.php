<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Player;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;
use Saloon\Enums\Method;

class CurrentlyPlayingRequest extends BaseRequest
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly ?string $market = null,
        private readonly ?array $additionalTypes = null
    ) {}

    public function resolveEndpoint(): string
    {
        return '/me/player/currently-playing';
    }

    protected function defaultQuery(): array
    {
        $query = [];

        if ($this->market) {
            $query['market'] = $this->market;
        }

        if ($this->additionalTypes) {
            $query['additional_types'] = implode(',', $this->additionalTypes);
        }

        return $query;
    }
}
