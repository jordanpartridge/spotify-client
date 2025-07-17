<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Resources;

use Jordanpartridge\SpotifyClient\Requests\Search\SearchAlbumsRequest;
use Jordanpartridge\SpotifyClient\Requests\Search\SearchAllRequest;
use Jordanpartridge\SpotifyClient\Requests\Search\SearchArtistsRequest;
use Jordanpartridge\SpotifyClient\Requests\Search\SearchPlaylistsRequest;
use Jordanpartridge\SpotifyClient\Requests\Search\SearchRequest;
use Jordanpartridge\SpotifyClient\Requests\Search\SearchTracksRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class SearchResource extends BaseResource
{
    private string $query = '';

    private ?string $market = null;

    private int $limit = 20;

    private int $offset = 0;

    private bool $includeExternal = false;

    /**
     * Set the search query.
     */
    public function query(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Set the market (country code).
     */
    public function market(string $market): self
    {
        $this->market = $market;

        return $this;
    }

    /**
     * Set the number of results to return.
     */
    public function limit(int $limit): self
    {
        $this->limit = min(50, max(1, $limit)); // Spotify API limits: 1-50

        return $this;
    }

    /**
     * Set the offset for pagination.
     */
    public function offset(int $offset): self
    {
        $this->offset = max(0, $offset);

        return $this;
    }

    /**
     * Include external audio content.
     */
    public function includeExternal(bool $include = true): self
    {
        $this->includeExternal = $include;

        return $this;
    }

    /**
     * Search for tracks only.
     */
    public function tracks(?string $query = null): Response
    {
        $searchQuery = $query ?? $this->query;

        return $this->connector->send(
            new SearchTracksRequest(
                $searchQuery,
                $this->market,
                $this->limit,
                $this->offset,
                $this->includeExternal
            )
        );
    }

    /**
     * Search for albums only.
     */
    public function albums(?string $query = null): Response
    {
        $searchQuery = $query ?? $this->query;

        return $this->connector->send(
            new SearchAlbumsRequest(
                $searchQuery,
                $this->market,
                $this->limit,
                $this->offset,
                $this->includeExternal
            )
        );
    }

    /**
     * Search for artists only.
     */
    public function artists(?string $query = null): Response
    {
        $searchQuery = $query ?? $this->query;

        return $this->connector->send(
            new SearchArtistsRequest(
                $searchQuery,
                $this->market,
                $this->limit,
                $this->offset,
                $this->includeExternal
            )
        );
    }

    /**
     * Search for playlists only.
     */
    public function playlists(?string $query = null): Response
    {
        $searchQuery = $query ?? $this->query;

        return $this->connector->send(
            new SearchPlaylistsRequest(
                $searchQuery,
                $this->market,
                $this->limit,
                $this->offset,
                $this->includeExternal
            )
        );
    }

    /**
     * Search across all content types.
     */
    public function all(?string $query = null): Response
    {
        $searchQuery = $query ?? $this->query;

        return $this->connector->send(
            new SearchAllRequest(
                $searchQuery,
                $this->market,
                $this->limit,
                $this->offset,
                $this->includeExternal
            )
        );
    }

    /**
     * Search with custom types.
     */
    public function custom(array $types, ?string $query = null): Response
    {
        $searchQuery = $query ?? $this->query;

        return $this->connector->send(
            new SearchRequest(
                $searchQuery,
                $types,
                $this->market,
                $this->limit,
                $this->offset,
                $this->includeExternal
            )
        );
    }

    /**
     * Quick search for the most relevant track.
     */
    public function firstTrack(string $query): ?array
    {
        $response = $this->limit(1)->tracks($query);
        $data = $response->json();

        return $data['tracks']['items'][0] ?? null;
    }

    /**
     * Search and get playable URI for first result.
     */
    public function getFirstTrackUri(string $query): ?string
    {
        $track = $this->firstTrack($query);

        return $track['uri'] ?? null;
    }
}
