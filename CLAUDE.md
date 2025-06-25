# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Spotify Client Package

This is a Laravel package that provides a clean, modern interface to the Spotify Web API using Saloon PHP HTTP client.

## Development Commands

```bash
# Install dependencies
composer install

# Code formatting and quality
./vendor/bin/pint                  # Format code with Laravel Pint
./vendor/bin/phpstan analyze       # Static analysis with PHPStan/Larastan

# Testing (when implemented)
./vendor/bin/pest                  # Run tests with Pest
./vendor/bin/pest --coverage       # Run tests with coverage
```

## Architecture Overview

The package follows a layered architecture inspired by the GitHub client pattern:

### Core Components

1. **SpotifyConnector** - Main Saloon connector that handles base URL and configuration
2. **SpotifyClient** - High-level client that implements the interface contract
3. **Resources** - Group related API endpoints (Albums, Artists, Tracks, etc.)
4. **Requests** - Individual API endpoint implementations
5. **Contracts** - Interface definitions stored in `src/Contracts/`

### Directory Structure

```
src/
├── Contracts/              # Interface definitions
│   ├── SpotifyClientInterface.php
│   └── AuthenticatorInterface.php
├── Resources/              # Grouped API endpoints
│   ├── BaseResource.php
│   ├── AlbumsResource.php
│   ├── ArtistsResource.php
│   ├── TracksResource.php
│   ├── PlaylistsResource.php
│   └── UsersResource.php
├── Requests/               # Individual API requests
│   ├── BaseRequest.php
│   ├── Albums/
│   ├── Artists/
│   ├── Tracks/
│   ├── Playlists/
│   └── Users/
├── SpotifyConnector.php    # Main Saloon connector
├── SpotifyClient.php       # Client implementation
└── SpotifyClientServiceProvider.php
```

## Saloon Integration

This package uses Saloon PHP for HTTP client functionality. Key patterns:

- **Connector** extends `Saloon\Http\Connector` with base URL and auth
- **Requests** extend `Saloon\Http\Request` for individual endpoints  
- **Resources** extend `Saloon\Http\BaseResource` to group related requests
- Uses traits like `AcceptsJson`, `AlwaysThrowOnErrors` for common functionality

See `docs/external-packages/saloon.md` for detailed Saloon usage patterns.

## Code Conventions

- Use strict types: `declare(strict_types=1);`
- Follow PSR-12 coding standards (enforced by Pint)
- Use readonly properties for immutable data
- Interface naming: `*Interface` (stored in Contracts directory)
- Resource naming: `*Resource` for API resource groups
- Request naming: `*Request` for individual API calls

## Authentication Patterns

The Spotify API supports multiple authentication flows:

1. **Client Credentials** - App-only access (no user context)
2. **Authorization Code** - User authorization required
3. **Bearer Token** - Direct token usage

Implement authentication in the `SpotifyConnector` using Saloon's auth patterns.

## API Endpoint Organization

Spotify API endpoints are organized by resource type:

- **Albums** - `/albums/*` endpoints
- **Artists** - `/artists/*` endpoints  
- **Tracks** - `/tracks/*` endpoints
- **Playlists** - `/playlists/*` endpoints
- **Users** - `/users/*` endpoints

Each resource should have a corresponding Resource class that groups related requests.

## Development Guidelines

1. **Type Safety** - Use proper type hints and return types
2. **Immutability** - Use readonly properties where appropriate  
3. **Error Handling** - Let Saloon handle HTTP errors via `AlwaysThrowOnErrors`
4. **Testing** - Use Saloon's mock client for testing API interactions
5. **Documentation** - Keep external package docs updated in `docs/external-packages/`

## Laravel Integration

The package provides:

- Service provider for Laravel integration
- Interface binding for dependency injection
- Configuration support (when needed)
- Facade support (when implemented)

## Package Development Workflow

1. Develop features following the established architectural patterns
2. Use Laravel Pint for code formatting
3. Run PHPStan for static analysis
4. Test with Saloon mock clients
5. Follow semantic versioning for releases