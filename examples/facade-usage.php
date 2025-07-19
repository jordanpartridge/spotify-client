<?php

/**
 * Spotify Facade Usage Examples
 *
 * Demonstrates the elegant Laravel Facade integration for seamless Spotify API access.
 * Clean, intuitive syntax that feels naturally Laravel! 🎵
 */

use Spotify; // The beautiful facade alias!

echo "🎵 Spotify Facade Examples - Laravel Elegance in Action!\n\n";

// 1. Search with beautiful facade syntax
echo "🔍 Searching for tracks...\n";
$searchResults = Spotify::search()->tracks('The Beatles', 5);
$tracks = $searchResults->json();
echo "Found {$tracks['tracks']['total']} tracks!\n";
foreach ($tracks['tracks']['items'] as $track) {
    echo "  🎵 {$track['name']} by {$track['artists'][0]['name']}\n";
}
echo "\n";

// 2. Get album information
echo "💿 Getting album details...\n";
$albumId = '1DFixLWuPkv3KT3TnV35m3';
$album = Spotify::albums()->get($albumId);
$albumData = $album->json();
echo "📀 Album: {$albumData['name']} by {$albumData['artists'][0]['name']}\n";
echo "🎵 {$albumData['total_tracks']} tracks, released {$albumData['release_date']}\n\n";

// 3. Library management with facade elegance
echo "📚 Managing your library...\n";
$trackId = '4iV5W9uYEdYUVa79Axb7Rh';

// Save a track
Spotify::library()->saveTrack($trackId);
echo "✅ Track saved to library\n";

// Check if saved
$isSaved = Spotify::library()->isTrackSaved($trackId);
$savedStatus = $isSaved->json()[0] ? 'Yes' : 'No';
echo "🔍 Is track saved? {$savedStatus}\n";

// Get saved tracks
$savedTracks = Spotify::library()->getSavedTracks(3);
$saved = $savedTracks->json();
echo "📖 You have {$saved['total']} saved tracks. Recent ones:\n";
foreach ($saved['items'] as $item) {
    $track = $item['track'];
    echo "  ❤️ {$track['name']} by {$track['artists'][0]['name']}\n";
}
echo "\n";

// 4. Artist discovery and following
echo "🎤 Discovering artists...\n";
$artistId = '0LcJLqbBmaGUft1e9Mm8HV'; // ABBA
$artist = Spotify::artists()->get($artistId);
$artistData = $artist->json();
echo "🌟 Artist: {$artistData['name']}\n";
echo '👥 Followers: '.number_format($artistData['followers']['total'])."\n";
echo '🎭 Genres: '.implode(', ', $artistData['genres'])."\n";

// Follow the artist
Spotify::library()->followArtist($artistId);
echo "✅ Now following {$artistData['name']}!\n\n";

// 5. Playlist management
echo "📋 Working with playlists...\n";
$userPlaylists = Spotify::playlists()->getCurrentUserPlaylists(5);
$playlists = $userPlaylists->json();
echo "📂 You have {$playlists['total']} playlists. Recent ones:\n";
foreach ($playlists['items'] as $playlist) {
    echo "  🎼 {$playlist['name']} ({$playlist['tracks']['total']} tracks)\n";
}
echo "\n";

// 6. Player control (if available)
echo "▶️ Player controls...\n";
try {
    $currentTrack = Spotify::player()->currentlyPlaying();
    if ($currentTrack->status() === 200) {
        $playing = $currentTrack->json();
        if ($playing['is_playing']) {
            $track = $playing['item'];
            echo "🎵 Now playing: {$track['name']} by {$track['artists'][0]['name']}\n";
        } else {
            echo "⏸️ Player is paused\n";
        }
    } else {
        echo "😴 No active playback session\n";
    }
} catch (Exception $e) {
    echo "🎧 Player not available (need premium account)\n";
}
echo "\n";

// 7. User profile
echo "👤 User profile...\n";
$profile = Spotify::users()->me();
$user = $profile->json();
echo "👋 Hello, {$user['display_name']}!\n";
echo "🌍 Country: {$user['country']}\n";
echo '🎵 Followers: '.number_format($user['followers']['total'])."\n\n";

echo "🎉 Facade usage complete! Laravel + Spotify = Musical Magic! ✨\n\n";

echo "💡 Benefits of the Facade:\n";
echo "  ✅ Clean, Laravel-native syntax\n";
echo "  ✅ No dependency injection needed\n";
echo "  ✅ IDE autocompletion support\n";
echo "  ✅ Feels naturally Laravel\n";
echo "  ✅ Reduces boilerplate code\n\n";

echo "🎵 The music never stops flowing with elegant facades! 🎶\n";
