# Spotify Web API Documentation

This document provides comprehensive information about the Spotify Web API for building the PHP client library.

## API Overview

The Spotify Web API enables applications to retrieve metadata from Spotify content and control playback. It follows RESTful design principles and requires OAuth 2.0 authentication.

**Base URL**: `https://api.spotify.com/v1`

## Authentication

### Supported Flow Types

1. **Authorization Code Flow** - For apps with server-side code that can store client secret
2. **Authorization Code with PKCE** - For mobile and desktop apps that cannot store client secret  
3. **Client Credentials Flow** - For server-to-server authentication (no user context)
4. **Implicit Grant Flow** - Deprecated, should not be used

### Access Tokens

All API requests require an access token in the Authorization header:
```
Authorization: Bearer {access_token}
```

## Core Endpoint Categories

### Albums

**Get Album**: `GET /albums/{id}`
- Retrieves detailed catalog information for a single album
- Parameters: `id` (required), `market` (optional)
- Returns: Album metadata, tracks, artists, copyright info

**Get Multiple Albums**: `GET /albums`
- Retrieves multiple albums by IDs
- Parameters: `ids` (comma-separated list), `market` (optional)
- Limit: Up to 20 albums per request

**Get Album Tracks**: `GET /albums/{id}/tracks`
- Retrieves tracks for a specific album
- Parameters: `id` (required), `market`, `limit`, `offset`

### Artists

**Get Artist**: `GET /artists/{id}`
- Retrieves detailed information about an artist
- Parameters: `id` (required)
- Returns: Artist metadata, genres, popularity, followers

**Get Multiple Artists**: `GET /artists`
- Retrieves multiple artists by IDs
- Parameters: `ids` (comma-separated list)
- Limit: Up to 50 artists per request

**Get Artist Albums**: `GET /artists/{id}/albums`
- Retrieves albums for a specific artist
- Parameters: `id` (required), `include_groups`, `market`, `limit`, `offset`

**Get Artist Top Tracks**: `GET /artists/{id}/top-tracks`
- Retrieves top tracks for an artist
- Parameters: `id` (required), `market`

**Get Related Artists**: `GET /artists/{id}/related-artists`
- Retrieves artists similar to a given artist
- Parameters: `id` (required)

### Tracks

**Get Track**: `GET /tracks/{id}`
- Retrieves detailed information about a track
- Parameters: `id` (required), `market` (optional)
- Returns: Track metadata, album info, artists, audio features

**Get Multiple Tracks**: `GET /tracks`
- Retrieves multiple tracks by IDs
- Parameters: `ids` (comma-separated list), `market` (optional)
- Limit: Up to 50 tracks per request

**Get Track Audio Features**: `GET /audio-features/{id}`
- Retrieves audio features for a track
- Returns: Danceability, energy, key, loudness, tempo, etc.

**Get Multiple Track Audio Features**: `GET /audio-features`
- Retrieves audio features for multiple tracks
- Parameters: `ids` (comma-separated list)

### Playlists

**Get Playlist**: `GET /playlists/{playlist_id}`
- Retrieves detailed information about a playlist
- Parameters: `playlist_id` (required), `market`, `fields`, `additional_types`

**Get Playlist Items**: `GET /playlists/{playlist_id}/tracks`
- Retrieves tracks from a playlist
- Parameters: `playlist_id` (required), `market`, `fields`, `limit`, `offset`

**Create Playlist**: `POST /users/{user_id}/playlists`
- Creates a new playlist for a user
- Requires user authorization scope

**Add Items to Playlist**: `POST /playlists/{playlist_id}/tracks`
- Adds tracks to a playlist
- Requires playlist modification scope

### Users

**Get User Profile**: `GET /users/{user_id}`
- Retrieves public profile information about a user
- Parameters: `user_id` (required)

**Get Current User Profile**: `GET /me`
- Retrieves profile information about the current user
- Requires user authorization

**Get User Playlists**: `GET /users/{user_id}/playlists`
- Retrieves playlists owned by a user
- Parameters: `user_id` (required), `limit`, `offset`

### Search

**Search**: `GET /search`
- Searches for albums, artists, playlists, tracks, shows, episodes, audiobooks
- Parameters: `q` (query), `type` (comma-separated), `market`, `limit`, `offset`, `include_external`

## Request/Response Patterns

### Common Query Parameters

- `market` - ISO 3166-1 alpha-2 country code (e.g., "US", "GB")
- `limit` - Maximum number of results (default varies by endpoint)
- `offset` - Index offset for pagination
- `fields` - Comma-separated list of fields to return (filtering)

### Response Format

All responses are in JSON format. Common response structure:

```json
{
  "href": "string",           // API URL
  "items": [],               // Array of objects
  "limit": 20,               // Request limit
  "next": "string",          // URL to next page
  "offset": 0,               // Current offset  
  "previous": null,          // URL to previous page
  "total": 100               // Total available items
}
```

### Error Responses

```json
{
  "error": {
    "status": 400,
    "message": "invalid id"
  }
}
```

## Rate Limiting

- Rate limits vary by endpoint and user type
- Use exponential backoff for retry logic
- Monitor response headers for rate limiting info
- Consider quota modes for high-volume applications

## Important Policies

1. **Content Policy**:
   - Cannot download or extract audio content
   - Visual content must remain in original form
   - Metadata must include attribution to Spotify

2. **Attribution Requirements**:
   - Display Spotify branding where required
   - Include proper attribution for metadata
   - Follow Spotify Design Guidelines

3. **Caching**:
   - Respect cache control headers
   - Update cached data regularly
   - Follow caching best practices

## Implementation Notes for PHP Client

### Market Parameter Strategy
Always include market parameter when available to ensure content availability in user's region.

### Pagination Handling
Implement consistent pagination support across all list endpoints using limit/offset pattern.

### Error Handling
Implement robust error handling for:
- 401 Unauthorized (token expired/invalid)
- 403 Forbidden (insufficient permissions)
- 404 Not Found (resource doesn't exist)
- 429 Too Many Requests (rate limited)
- 500+ Server Errors

### Scopes for Authorization
Different endpoints require different OAuth scopes:
- `user-read-private` - Access to user's private profile
- `playlist-modify-public` - Modify public playlists
- `playlist-modify-private` - Modify private playlists
- `user-library-read` - Access user's library
- `user-library-modify` - Modify user's library

### Content Types
- Request: `application/json`
- Response: `application/json`
- Use proper Content-Type headers for POST/PUT requests