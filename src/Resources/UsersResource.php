<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Resources;

use Jordanpartridge\SpotifyClient\Requests\Users\GetUserProfileRequest;
use Saloon\Http\Response;

class UsersResource extends BaseResource
{
    public function getProfile(string $userId): Response
    {
        return $this->connector->send(new GetUserProfileRequest($userId));
    }
}