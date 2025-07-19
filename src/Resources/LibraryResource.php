<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Resources;

use Jordanpartridge\SpotifyClient\Requests\Library\CheckFollowingRequest;
use Jordanpartridge\SpotifyClient\Requests\Library\CheckSavedAlbumsRequest;
use Jordanpartridge\SpotifyClient\Requests\Library\CheckSavedTracksRequest;
use Jordanpartridge\SpotifyClient\Requests\Library\FollowRequest;
use Jordanpartridge\SpotifyClient\Requests\Library\GetFollowedArtistsRequest;
use Jordanpartridge\SpotifyClient\Requests\Library\GetSavedAlbumsRequest;
use Jordanpartridge\SpotifyClient\Requests\Library\GetSavedTracksRequest;
use Jordanpartridge\SpotifyClient\Requests\Library\RemoveAlbumsRequest;
use Jordanpartridge\SpotifyClient\Requests\Library\RemoveTracksRequest;
use Jordanpartridge\SpotifyClient\Requests\Library\SaveAlbumsRequest;
use Jordanpartridge\SpotifyClient\Requests\Library\SaveTracksRequest;
use Jordanpartridge\SpotifyClient\Requests\Library\UnfollowRequest;
use Saloon\Http\Response;

class LibraryResource extends BaseResource
{
    // Track Management - Keep the music flowing! 🎵

    public function saveTrack(string $trackId): Response
    {
        return $this->connector->send(new SaveTracksRequest([$trackId]));
    }

    public function saveTracks(array $trackIds): Response
    {
        return $this->connector->send(new SaveTracksRequest($trackIds));
    }

    public function removeTrack(string $trackId): Response
    {
        return $this->connector->send(new RemoveTracksRequest([$trackId]));
    }

    public function removeTracks(array $trackIds): Response
    {
        return $this->connector->send(new RemoveTracksRequest($trackIds));
    }

    public function getSavedTracks(int $limit = 20, int $offset = 0, ?string $market = null): Response
    {
        return $this->connector->send(new GetSavedTracksRequest($limit, $offset, $market));
    }

    public function isTrackSaved(string $trackId): Response
    {
        return $this->connector->send(new CheckSavedTracksRequest([$trackId]));
    }

    public function areTracksSaved(array $trackIds): Response
    {
        return $this->connector->send(new CheckSavedTracksRequest($trackIds));
    }

    // Album Management - For those full album experiences! 💿

    public function saveAlbum(string $albumId): Response
    {
        return $this->connector->send(new SaveAlbumsRequest([$albumId]));
    }

    public function saveAlbums(array $albumIds): Response
    {
        return $this->connector->send(new SaveAlbumsRequest($albumIds));
    }

    public function removeAlbum(string $albumId): Response
    {
        return $this->connector->send(new RemoveAlbumsRequest([$albumId]));
    }

    public function removeAlbums(array $albumIds): Response
    {
        return $this->connector->send(new RemoveAlbumsRequest($albumIds));
    }

    public function getSavedAlbums(int $limit = 20, int $offset = 0, ?string $market = null): Response
    {
        return $this->connector->send(new GetSavedAlbumsRequest($limit, $offset, $market));
    }

    public function isAlbumSaved(string $albumId): Response
    {
        return $this->connector->send(new CheckSavedAlbumsRequest([$albumId]));
    }

    public function areAlbumsSaved(array $albumIds): Response
    {
        return $this->connector->send(new CheckSavedAlbumsRequest($albumIds));
    }

    // Follow Management - Connect with your favorite artists! 🎤

    public function followArtist(string $artistId): Response
    {
        return $this->connector->send(new FollowRequest('artist', [$artistId]));
    }

    public function followArtists(array $artistIds): Response
    {
        return $this->connector->send(new FollowRequest('artist', $artistIds));
    }

    public function followUser(string $userId): Response
    {
        return $this->connector->send(new FollowRequest('user', [$userId]));
    }

    public function followUsers(array $userIds): Response
    {
        return $this->connector->send(new FollowRequest('user', $userIds));
    }

    public function unfollowArtist(string $artistId): Response
    {
        return $this->connector->send(new UnfollowRequest('artist', [$artistId]));
    }

    public function unfollowArtists(array $artistIds): Response
    {
        return $this->connector->send(new UnfollowRequest('artist', $artistIds));
    }

    public function unfollowUser(string $userId): Response
    {
        return $this->connector->send(new UnfollowRequest('user', [$userId]));
    }

    public function unfollowUsers(array $userIds): Response
    {
        return $this->connector->send(new UnfollowRequest('user', $userIds));
    }

    public function getFollowedArtists(int $limit = 20, ?string $after = null): Response
    {
        return $this->connector->send(new GetFollowedArtistsRequest($limit, $after));
    }

    public function isFollowingArtist(string $artistId): Response
    {
        return $this->connector->send(new CheckFollowingRequest('artist', [$artistId]));
    }

    public function areFollowingArtists(array $artistIds): Response
    {
        return $this->connector->send(new CheckFollowingRequest('artist', $artistIds));
    }

    public function isFollowingUser(string $userId): Response
    {
        return $this->connector->send(new CheckFollowingRequest('user', [$userId]));
    }

    public function areFollowingUsers(array $userIds): Response
    {
        return $this->connector->send(new CheckFollowingRequest('user', $userIds));
    }
}
