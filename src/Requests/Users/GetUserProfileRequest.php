<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Requests\Users;

use Jordanpartridge\SpotifyClient\Requests\BaseRequest;

class GetUserProfileRequest extends BaseRequest
{
    public function __construct(
        private readonly string $userId
    ) {}

    public function resolveEndpoint(): string
    {
        return "/users/{$this->userId}";
    }
}
