# Spotify Commands

The Spotify client package provides an organized command structure for easy discovery and functionality grouping. All commands follow Laravel conventions with clear namespacing and autocomplete support. 🎵

## Command Overview

Commands are organized into logical groups for better discoverability:

```bash
# Authentication Commands
spotify:auth:setup      # Set up Spotify API authentication
spotify:auth:status     # Check authentication status
spotify:auth:refresh    # Refresh authentication tokens

# Player Control Commands  
spotify:player:status   # Show current playback status
spotify:player:play     # Start/resume playback
spotify:player:pause    # Pause playback

# Library Management Commands
spotify:library:sync    # Sync library data locally

# Legacy Commands (for backward compatibility)
spotify:install         # Alias for spotify:auth:setup
spotify:setup           # Alias for spotify:auth:setup
```

## Authentication Commands

### `spotify:auth:setup`

Set up Spotify API authentication with an interactive guided experience.

```bash
php artisan spotify:auth:setup
```

**Features:**
- 🧭 Guided setup walkthrough
- 📱 Automatic Spotify Developer Dashboard opening
- ✅ Credential validation
- ⚙️ Multiple authentication flow support
- 🔧 Automatic .env configuration

**Example Output:**
```
🎵 Spotify Client Setup - Let's get the music flowing!

🔍 Detected application type: laravel

How would you like to set up Spotify credentials?
› 🧭 Guided setup (recommended)
  ⚙️ Manual setup  
  ⏭️ Skip setup

✅ Credentials validated successfully!
🎉 Spotify setup complete! The music is ready to flow! 🎵
```

### `spotify:auth:status`

Check your current Spotify authentication configuration and test connectivity.

```bash
php artisan spotify:auth:status
```

**Features:**
- ✅ Configuration validation
- 🔑 Authentication testing
- 🎫 Token status display
- 📊 Connection diagnostics

**Example Output:**
```
🔍 Spotify Authentication Status

✅ Configuration Status
┌─────────────┬──────────────────────────┐
│ Setting     │ Status                   │
├─────────────┼──────────────────────────┤
│ Client ID   │ ✅ Configured (12345...) │
│ Auth Flow   │ ✅ client_credentials    │
│ Token Stor… │ file                     │
└─────────────┴──────────────────────────┘

🔑 Testing Authentication...
✅ Authentication working correctly!
🎵 Spotify authentication is ready to rock!
```

### `spotify:auth:refresh`

Manually refresh authentication tokens.

```bash
# Refresh all available tokens
php artisan spotify:auth:refresh

# Refresh specific flow
php artisan spotify:auth:refresh --flow=client_credentials
php artisan spotify:auth:refresh --flow=authorization_code
```

**Features:**
- 🔄 Token refresh for all flows
- 🎯 Specific flow targeting
- ✅ Automatic expiration detection
- 📊 Refresh status reporting

## Player Control Commands

### `spotify:player:status`

Display current Spotify playback status and track information.

```bash
php artisan spotify:player:status
```

**Features:**
- 🎵 Now playing information
- ⏸️ Playback state display
- 📊 Progress and duration
- 🔊 Volume and device info
- 🎮 Available controls list

**Example Output:**
```
🎵 Spotify Player Status

🎵 Now Playing
┌──────────┬─────────────────────────────┐
│ Property │ Value                       │
├──────────┼─────────────────────────────┤
│ Track    │ Sweet Caroline              │
│ Artist   │ Neil Diamond                │
│ Album    │ Brother Love's Travelling   │
│ Status   │ ▶️ Playing                  │
│ Progress │ 1:23 / 3:45                │
│ Device   │ MacBook Pro Speakers        │
└──────────┴─────────────────────────────┘

🎮 Available Controls
Use these commands to control playback:
  php artisan spotify:player:play     - Resume playback
  php artisan spotify:player:pause    - Pause playback
```

### `spotify:player:play`

Start or resume Spotify playback.

```bash
# Resume current playback
php artisan spotify:player:play

# Play specific content
php artisan spotify:player:play --uri=spotify:track:4iV5W9uYEdYUVa79Axb7Rh
php artisan spotify:player:play --uri=spotify:album:1DFixLWuPkv3KT3TnV35m3
php artisan spotify:player:play --uri=spotify:playlist:37i9dQZF1DXcBWIGoYBM5M
```

**Options:**
- `--uri`: Spotify URI to play (track, album, playlist)

### `spotify:player:pause`

Pause current Spotify playback.

```bash
php artisan spotify:player:pause
```

## Library Management Commands

### `spotify:library:sync`

Sync your Spotify library data locally for analysis and insights.

```bash
# Sync default number of tracks (50)
php artisan spotify:library:sync

# Sync specific number of tracks
php artisan spotify:library:sync --limit=100
```

**Features:**
- 📚 Library data synchronization
- 📊 Music statistics analysis
- 🎭 Genre discovery
- 👥 Artist and album collection
- 💡 Music preference insights

**Example Output:**
```
📚 Syncing Spotify Library

Syncing library data... ████████████████████ 100%

📊 Library Statistics
┌─────────────────┬───────┐
│ Metric          │ Count │
├─────────────────┼───────┤
│ Total Tracks    │ 100   │
│ Unique Artists  │ 45    │
│ Unique Albums   │ 78    │
│ Genres Found    │ 12    │
└─────────────────┴───────┘

🎭 Top Genres Found:
  • pop
  • rock
  • indie
```

**Options:**
- `--limit`: Number of tracks to sync (default: 50)

## Legacy Commands

For backward compatibility, these commands redirect to the new organized structure:

### `spotify:install`
Alias for `spotify:auth:setup` - maintains compatibility with existing scripts.

### `spotify:setup`  
Alias for `spotify:auth:setup` - provides familiar command name.

## Command Discovery

Use Laravel's built-in command listing to discover all available Spotify commands:

```bash
# List all Spotify commands
php artisan list spotify

# Autocomplete support (type and press Tab)
php artisan spotify:<TAB>
php artisan spotify:auth:<TAB>
php artisan spotify:player:<TAB>
```

## Command Conventions

All Spotify commands follow consistent patterns:

- **Namespace Structure**: `spotify:{group}:{action}`
- **Descriptive Names**: Clear action indication
- **Consistent Output**: Beautiful prompts and tables
- **Error Handling**: Graceful error messages and help
- **Status Codes**: Proper exit codes for scripting

## Integration Examples

### CI/CD Pipeline
```bash
# Validate Spotify setup in CI
php artisan spotify:auth:status || exit 1
```

### Music Analytics Script
```bash
#!/bin/bash
echo "🎵 Generating music analytics..."
php artisan spotify:library:sync --limit=500
php artisan spotify:auth:status
echo "✅ Analytics complete!"
```

### Development Workflow
```bash
# Quick setup for new developers
php artisan spotify:auth:setup
php artisan spotify:auth:status
php artisan spotify:player:status
```

## Command Structure Benefits

### ✅ **Organized Discovery**
Commands are logically grouped by functionality, making them easy to find.

### ✅ **Autocomplete Support**  
Tab completion works perfectly with the namespace structure.

### ✅ **Clear Responsibility**
Each command has a single, clear purpose with obvious naming.

### ✅ **Scalable Structure**
Easy to add new command groups and maintain organization.

### ✅ **Laravel Conventions**
Follows established Laravel command naming patterns.

### ✅ **Backward Compatibility**
Legacy commands continue working while new structure is available.

---

🎵 **Keep the music flowing with organized, discoverable commands!** 🎶