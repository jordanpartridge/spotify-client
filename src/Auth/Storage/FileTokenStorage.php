<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Auth\Storage;

use Illuminate\Filesystem\Filesystem;
use Jordanpartridge\SpotifyClient\Contracts\TokenStorageInterface;

class FileTokenStorage implements TokenStorageInterface
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $path
    ) {}

    public function store(string $key, array $token): void
    {
        $tokens = $this->loadTokens();
        $tokens[$key] = $token;
        $this->saveTokens($tokens);
    }

    public function retrieve(string $key): ?array
    {
        $tokens = $this->loadTokens();

        return $tokens[$key] ?? null;
    }

    public function remove(string $key): void
    {
        $tokens = $this->loadTokens();
        unset($tokens[$key]);
        $this->saveTokens($tokens);
    }

    public function exists(string $key): bool
    {
        $tokens = $this->loadTokens();

        return isset($tokens[$key]);
    }

    private function loadTokens(): array
    {
        if (! $this->files->exists($this->path)) {
            return [];
        }

        $content = $this->files->get($this->path);

        return json_decode($content, true) ?: [];
    }

    private function saveTokens(array $tokens): void
    {
        $directory = dirname($this->path);
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $this->files->put($this->path, json_encode($tokens, JSON_PRETTY_PRINT));
    }
}
