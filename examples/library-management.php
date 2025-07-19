<?php

/**
 * User Library Management Examples
 * 
 * Demonstrates how to save tracks, albums, follow artists, and manage your personal Spotify library.
 * This keeps the music flowing and helps users build their perfect collection! 🎵
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Jordanpartridge\SpotifyClient\Contracts\SpotifyClientInterface;

// Get the Spotify client (assumes proper authentication setup)
$spotify = app(SpotifyClientInterface::class);

echo "🎵 Spotify Library Management Examples\n\n";

// 1. Save your favorite tracks to your library
echo "💾 Saving tracks to library...\n";
$trackId = '4iV5W9uYEdYUVa79Axb7Rh'; // "Sweet Caroline" by Neil Diamond
$response = $spotify->library()->saveTrack($trackId);
echo "✅ Track saved successfully!\n\n";

// 2. Save multiple tracks at once
echo "💾 Saving multiple tracks...\n";
$trackIds = [
    '4iV5W9uYEdYUVa79Axb7Rh', // Sweet Caroline
    '0VjIjW4GlUZAMYd2vXMi3b', // Blinding Lights
    '4uLU6hMCjMI75M1A2tKUQC'  // Never Gonna Give You Up
];
$response = $spotify->library()->saveTracks($trackIds);
echo "✅ Multiple tracks saved!\n\n";

// 3. Check if tracks are saved
echo "🔍 Checking if tracks are saved...\n";
$response = $spotify->library()->areTracksSaved($trackIds);
$saved = $response->json();
foreach ($trackIds as $index => $trackId) {
    $status = $saved[$index] ? '✅ Saved' : '❌ Not saved';
    echo "  Track {$trackId}: {$status}\n";
}
echo "\n";

// 4. Get your saved tracks
echo "📚 Getting saved tracks...\n";
$response = $spotify->library()->getSavedTracks(5); // Get first 5
$savedTracks = $response->json();
echo "📖 Found {$savedTracks['total']} saved tracks\n";
foreach ($savedTracks['items'] as $item) {
    $track = $item['track'];
    echo "  🎵 {$track['name']} by {$track['artists'][0]['name']}\n";
}
echo "\n";

// 5. Save albums to your library
echo "💿 Saving albums...\n";
$albumId = '1DFixLWuPkv3KT3TnV35m3'; // Dark Side of the Moon
$response = $spotify->library()->saveAlbum($albumId);
echo "✅ Album saved!\n\n";

// 6. Follow your favorite artists
echo "👥 Following artists...\n";
$artistId = '0LcJLqbBmaGUft1e9Mm8HV'; // ABBA
$response = $spotify->library()->followArtist($artistId);
echo "✅ Now following artist!\n\n";

// 7. Check if following artists
echo "🔍 Checking follow status...\n";
$response = $spotify->library()->isFollowingArtist($artistId);
$following = $response->json()[0];
echo $following ? "✅ Following artist" : "❌ Not following artist";
echo "\n\n";

// 8. Get followed artists
echo "👥 Getting followed artists...\n";
$response = $spotify->library()->getFollowedArtists(5);
$followedArtists = $response->json();
echo "📖 Following {$followedArtists['artists']['total']} artists\n";
foreach ($followedArtists['artists']['items'] as $artist) {
    echo "  🎤 {$artist['name']} ({$artist['followers']['total']} followers)\n";
}
echo "\n";

// 9. Remove tracks from library (cleanup)
echo "🗑️ Cleaning up - removing tracks...\n";
$response = $spotify->library()->removeTracks([$trackId]);
echo "✅ Track removed from library\n\n";

echo "🎉 Library management complete! Your music collection is perfectly curated! 🎵\n";