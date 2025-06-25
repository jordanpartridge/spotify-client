# Saloon PHP HTTP Client

This document provides guidance on using Saloon PHP HTTP client within this Spotify client package.

## Architecture Overview

Saloon follows a structured approach to building API clients:

### Core Components

1. **Connector** - Main entry point that defines the base URL and shared configuration
2. **Request** - Individual API endpoint implementations  
3. **Resource** - Groups related requests together
4. **Response** - Handles API response data

## Key Classes and Interfaces

### Base Classes
- `Saloon\Http\Connector` - Extend this for your API client
- `Saloon\Http\Request` - Extend this for individual API requests
- `Saloon\Http\BaseResource` - Extend this to group related endpoints

### Authentication
- `Saloon\Http\Auth\TokenAuthenticator` - Bearer token authentication
- OAuth2 traits for complex auth flows

### Request/Response Handling
- `Saloon\Traits\Plugins\AcceptsJson` - Sets JSON Accept header
- `Saloon\Traits\Plugins\AlwaysThrowOnErrors` - Throws exceptions on HTTP errors
- `Saloon\Traits\Body\HasJsonBody` - For JSON request bodies

## Common Patterns

### Connector Implementation
```php
class SpotifyConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    public function resolveBaseUrl(): string
    {
        return 'https://api.spotify.com/v1';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
```

### Request Implementation
```php
class GetAlbumRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $id
    ) {}

    public function resolveEndpoint(): string
    {
        return "/albums/{$this->id}";
    }
}
```

### Resource Implementation
```php
class AlbumsResource extends BaseResource
{
    public function get(string $id): Response
    {
        return $this->connector->send(new GetAlbumRequest($id));
    }
}
```

## Authentication Patterns

### Bearer Token
```php
// In your connector
protected function defaultAuth(): AuthenticatorInterface
{
    return new TokenAuthenticator(config('spotify.access_token'));
}
```

### OAuth2 Client Credentials
```php
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Traits\OAuth2\ClientCredentialsGrant;

class SpotifyConnector extends Connector
{
    use ClientCredentialsGrant;

    protected function defaultAuth(): AuthenticatorInterface
    {
        return new TokenAuthenticator($this->getAccessToken());
    }
}
```

## Response Handling

### JSON Responses
```php
$response = $connector->send(new GetAlbumRequest('album-id'));

// Get as array
$data = $response->json();

// Get as object
$album = $response->object();

// Check status
if ($response->successful()) {
    // Handle success
}
```

### Data Transfer Objects
```php
// Using Spatie Laravel Data
$album = AlbumDto::from($response->json());
```

## Error Handling

### Built-in Error Handling
```php
// Using AlwaysThrowOnErrors trait
try {
    $response = $connector->send(new GetAlbumRequest('invalid-id'));
} catch (RequestException $e) {
    // Handle 4xx/5xx errors
}
```

### Custom Error Handling
```php
public function hasRequestFailed(Response $response): ?bool
{
    return $response->status() >= 400;
}

public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
{
    return new SpotifyApiException($response);
}
```

## Testing

### Mock Responses
```php
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

$mockClient = new MockClient([
    GetAlbumRequest::class => MockResponse::make(['id' => 'album-123']),
]);

$connector = new SpotifyConnector();
$connector->withMockClient($mockClient);
```

## Best Practices

1. **Use Type Hints** - Always use proper type hints for parameters and return types
2. **Resource Organization** - Group related endpoints in Resources
3. **Authentication** - Handle auth at the Connector level
4. **Error Handling** - Use consistent error handling patterns
5. **Testing** - Mock external API calls in tests
6. **Documentation** - Document your API endpoints and expected responses

## Common Spotify API Patterns

### Pagination
```php
protected function defaultQuery(): array
{
    return [
        'limit' => 20,
        'offset' => 0,
    ];
}
```

### Market/Country Support
```php
protected function defaultQuery(): array
{
    return [
        'market' => 'US',
    ];
}
```

### Multiple IDs
```php
protected function defaultQuery(): array
{
    return [
        'ids' => implode(',', $this->ids),
    ];
}
```