<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Resources;

use Jordanpartridge\SpotifyClient\Requests\Player\CurrentlyPlayingRequest;
use Jordanpartridge\SpotifyClient\Requests\Player\NextRequest;
use Jordanpartridge\SpotifyClient\Requests\Player\PauseRequest;
use Jordanpartridge\SpotifyClient\Requests\Player\PlayRequest;
use Jordanpartridge\SpotifyClient\Requests\Player\PreviousRequest;
use Jordanpartridge\SpotifyClient\Requests\Player\VolumeRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class PlayerResource extends BaseResource
{
    /**
     * Start or resume playback on the user's active device.
     */
    public function play(?array $uris = null, ?string $contextUri = null, ?int $positionMs = null, ?string $deviceId = null): Response
    {
        return $this->connector->send(new PlayRequest($uris, $contextUri, $positionMs, $deviceId));
    }

    /**
     * Pause playback on the user's account.
     */
    public function pause(?string $deviceId = null): Response
    {
        return $this->connector->send(new PauseRequest($deviceId));
    }

    /**
     * Skip to the next track in the user's queue.
     */
    public function next(?string $deviceId = null): Response
    {
        return $this->connector->send(new NextRequest($deviceId));
    }

    /**
     * Skip to the previous track in the user's queue.
     */
    public function previous(?string $deviceId = null): Response
    {
        return $this->connector->send(new PreviousRequest($deviceId));
    }

    /**
     * Set the volume for the user's current playback device.
     */
    public function volume(int $volumePercent, ?string $deviceId = null): Response
    {
        return $this->connector->send(new VolumeRequest($volumePercent, $deviceId));
    }

    /**
     * Get information about the user's current playback state.
     */
    public function currentlyPlaying(?string $market = null, ?array $additionalTypes = null): Response
    {
        return $this->connector->send(new CurrentlyPlayingRequest($market, $additionalTypes));
    }

    /**
     * Resume playback on the user's active device.
     */
    public function resume(?string $deviceId = null): Response
    {
        return $this->play(deviceId: $deviceId);
    }

    /**
     * Play specific tracks by their URIs.
     */
    public function playTracks(array $trackUris, ?string $deviceId = null): Response
    {
        return $this->play($trackUris, deviceId: $deviceId);
    }

    /**
     * Play a playlist, album, or artist by context URI.
     */
    public function playContext(string $contextUri, ?int $positionMs = null, ?string $deviceId = null): Response
    {
        return $this->play(contextUri: $contextUri, positionMs: $positionMs, deviceId: $deviceId);
    }
}