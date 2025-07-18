# OAuth Flows Documentation

## 🔐 Spotify OAuth Implementation

This package supports both OAuth flows required by Spotify's Web API with modern security practices.

## Flow Types

### 1. Client Credentials Flow
**Use Case**: App-only access, no user interaction required
**Best For**: 
- Background services
- Data analysis
- Public catalog access

```php
$tokens = $oauthHandler->getClientCredentialsToken(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret'
);

// Returns:
[
    'access_token' => 'BQC6FWqqrh...',
    'token_type' => 'Bearer',
    'expires_in' => 3600,
    'expires_at' => 1642678800
]
```

### 2. Authorization Code Flow with PKCE
**Use Case**: User authorization required
**Best For**:
- User-specific data access
- Playlist modification
- Personal library management

```php
// Generate authorization URL
$authData = $oauthHandler->generateAuthorizationUrlWithPKCE(
    clientId: 'your-client-id',
    redirectUri: 'http://localhost:8080/callback',
    scopes: ['user-read-private', 'playlist-read-private']
);

// Open browser
$oauthHandler->openBrowser($authData['url']);

// Exchange code for tokens
$tokens = $oauthHandler->exchangeCodeForTokens($appConfig);
```

## Security Implementation

### PKCE (Proof Key for Code Exchange)
All authorization code flows use PKCE for enhanced security:

```php
// Code verifier generation (RFC 7636)
private function generateCodeVerifier(): string
{
    return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
}

// Code challenge generation
private function generateCodeChallenge(string $codeVerifier): string
{
    return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
}
```

### State Parameter
CSRF protection through state parameter validation:

```php
// State generation
$this->state = bin2hex(random_bytes(16));

// State validation
public function validateState(string $receivedState): bool
{
    return $this->state && hash_equals($this->state, $receivedState);
}
```

## Local OAuth Server

### ReactPHP Implementation
For CLI applications, we run a local HTTP server to capture OAuth callbacks:

```php
public function startCallbackServer(int $port = 8080, string $host = '127.0.0.1'): string
{
    $loop = Loop::get();
    
    $server = new HttpServer(function (ServerRequestInterface $request) {
        return $this->handleCallback($request);
    });

    $socket = new SocketServer("{$host}:{$port}", [], $loop);
    $server->listen($socket);

    // Store the redirect URI for consistent token exchange
    $this->redirectUri = "http://{$host}:{$port}/callback";

    return $this->redirectUri;
}
```

### Callback Handling
Smart callback processing with error handling:

```php
private function handleCallback(ServerRequestInterface $request): Response
{
    $query = $request->getQueryParams();
    
    // Success: code + state
    if (isset($query['code']) && isset($query['state'])) {
        if (!$this->validateState($query['state'])) {
            return new Response(400, [], $this->generateErrorPage('Invalid state'));
        }
        
        $this->authorizationCode = $query['code'];
        return new Response(200, [], $this->generateSuccessPage());
    }
    
    // Error: error + error_description
    if (isset($query['error'])) {
        $error = $query['error'];
        $description = $query['error_description'] ?? 'Unknown error';
        return new Response(400, [], $this->generateErrorPage("Auth failed: {$error}"));
    }
    
    // Default: waiting page
    return new Response(200, [], $this->generateCallbackPage());
}
```

## Token Management

### Token Structure
Standardized token format across all flows:

```php
[
    'access_token' => 'BQC6FWqq...',      // Required: API access token
    'token_type' => 'Bearer',             // Always 'Bearer'
    'expires_in' => 3600,                 // Seconds until expiration
    'refresh_token' => 'AQC8vN...',       // Optional: for refresh
    'scope' => 'user-read-private',       // Granted scopes
    'expires_at' => 1642678800            // Calculated expiration timestamp
]
```

### Token Refresh (Future Enhancement)
```php
public function refreshToken(string $refreshToken, array $appConfig): array
{
    $request = new RefreshTokenRequest(
        $refreshToken,
        $appConfig['client_id'],
        $appConfig['client_secret']
    );
    
    $response = $this->authConnector->send($request);
    $data = $response->json();
    
    return [
        'access_token' => $data['access_token'],
        'token_type' => $data['token_type'],
        'expires_in' => $data['expires_in'],
        'refresh_token' => $data['refresh_token'] ?? $refreshToken,
        'scope' => $data['scope'] ?? null,
        'expires_at' => time() + $data['expires_in'],
    ];
}
```

## Scope Management

### Available Scopes
```php
$availableScopes = [
    // User Profile
    'user-read-private' => 'Read user profile data',
    'user-read-email' => 'Read user email address',
    
    // Playlists
    'playlist-read-private' => 'Read private playlists',
    'playlist-read-collaborative' => 'Read collaborative playlists',
    'playlist-modify-public' => 'Modify public playlists',
    'playlist-modify-private' => 'Modify private playlists',
    
    // Library
    'user-library-read' => 'Read saved tracks and albums',
    'user-library-modify' => 'Save/remove tracks and albums',
    
    // Listening History
    'user-top-read' => 'Read top artists and tracks',
    'user-read-recently-played' => 'Read recently played tracks',
    
    // Playback
    'user-read-playback-state' => 'Read current playback state',
    'user-modify-playback-state' => 'Control playback (play/pause/skip)',
];
```

### Scope Validation
```php
public function validateScopes(array $requestedScopes, array $grantedScopes): array
{
    $missing = array_diff($requestedScopes, $grantedScopes);
    $granted = array_intersect($requestedScopes, $grantedScopes);
    
    return [
        'valid' => empty($missing),
        'granted' => $granted,
        'missing' => $missing,
        'message' => empty($missing) 
            ? 'All requested scopes granted'
            : 'Missing scopes: ' . implode(', ', $missing)
    ];
}
```

## Error Handling

### Common OAuth Errors
```php
// Authorization errors
'access_denied' => 'User denied authorization',
'invalid_client' => 'Invalid client credentials',
'invalid_grant' => 'Invalid authorization code',
'invalid_request' => 'Malformed request',
'invalid_scope' => 'Invalid or excessive scope',
'server_error' => 'Spotify server error',
'temporarily_unavailable' => 'Service temporarily unavailable',

// Token errors  
'invalid_token' => 'Token expired or malformed',
'insufficient_scope' => 'Token lacks required permissions',
```

### Error Recovery
```php
try {
    $tokens = $oauthHandler->exchangeCodeForTokens($appConfig);
} catch (RequestException $e) {
    if ($e->getCode() === 400) {
        // Handle invalid_grant - code expired
        throw new \Exception('Authorization code expired. Please restart the flow.');
    }
    
    if ($e->getCode() === 401) {
        // Handle invalid_client
        throw new \Exception('Invalid client credentials. Check your app settings.');
    }
    
    throw new \Exception("OAuth failed: {$e->getMessage()}");
}
```

## Browser Integration

### Cross-Platform Browser Opening
```php
public function openBrowser(string $url): void
{
    $command = match (PHP_OS_FAMILY) {
        'Darwin' => "open '{$url}'",
        'Linux' => "xdg-open '{$url}'",
        'Windows' => "start '{$url}'",
        default => null,
    };

    if ($command) {
        exec($command . ' > /dev/null 2>&1 &');
    }
}
```

### User Experience
The OAuth flow provides visual feedback:

1. **Authorization Page**: Clean waiting interface
2. **Success Page**: Confirmation with auto-close
3. **Error Page**: Clear error messages and recovery steps

## Testing OAuth Flows

### Mock Authentication
```php
// In tests
$mockConnector = Mockery::mock(SpotifyAuthConnector::class);
$mockConnector->shouldReceive('send')
    ->andReturn(new MockResponse([
        'access_token' => 'mock-token',
        'token_type' => 'Bearer',
        'expires_in' => 3600
    ]));
```

### Integration Testing
```php
public function test_authorization_code_flow()
{
    $handler = new OAuthFlowHandler($this->authConnector);
    
    // Test URL generation
    $authUrl = $handler->generateAuthorizationUrl(
        'test-client-id',
        'http://localhost:8080/callback',
        ['user-read-private']
    );
    
    $this->assertStringContains('accounts.spotify.com/authorize', $authUrl);
    $this->assertStringContains('client_id=test-client-id', $authUrl);
}
```

---

**Next**: [Token Management](token-management.md)
**Related**: [Client Credentials](client-credentials.md) | [Authorization Code](authorization-code.md)