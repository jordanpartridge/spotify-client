<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Commands\Auth;

use Illuminate\Console\Command;
use Jordanpartridge\SpotifyClient\Services\CodeGenerator;
use Jordanpartridge\SpotifyClient\Services\CredentialValidator;
use Jordanpartridge\SpotifyClient\Services\EnvironmentDetector;
use Jordanpartridge\SpotifyClient\Services\OAuthFlowHandler;
use Jordanpartridge\SpotifyClient\Services\SpotifyAppManager;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class SetupCommand extends Command
{
    protected $signature = 'spotify:auth:setup';

    protected $description = 'Set up Spotify API authentication with zero manual steps - the most developer-friendly setup experience ever built';

    public function __construct(
        private readonly EnvironmentDetector $environmentDetector,
        private readonly SpotifyAppManager $appManager,
        private readonly OAuthFlowHandler $oauthHandler,
        private readonly CredentialValidator $validator,
        private readonly CodeGenerator $codeGenerator,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        intro('🎵 Spotify Client Setup - Let\'s get the music flowing!');

        // Check if already configured
        if ($this->isAlreadyConfigured()) {
            $shouldReconfigure = confirm('Spotify is already configured. Would you like to reconfigure?');
            if (! $shouldReconfigure) {
                outro('🎵 Setup cancelled. Your existing configuration is ready to rock!');

                return self::SUCCESS;
            }
        }

        try {
            // Detect environment and configure accordingly
            $appType = $this->environmentDetector->detectApplicationType();
            info("🔍 Detected application type: {$appType}");

            $setupResult = match ($appType) {
                'laravel' => $this->setupLaravelApp(),
                'laravel-zero' => $this->setupLaravelZeroApp(),
                'package-development' => $this->setupPackageDevelopment(),
                default => $this->setupGenericPHPApp(),
            };

            if ($setupResult === self::SUCCESS) {
                outro('🎉 Spotify setup complete! The music is ready to flow! 🎵');
                $this->displayQuickStart();
            }

            return $setupResult;

        } catch (\Exception $e) {
            error("Setup failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function isAlreadyConfigured(): bool
    {
        return ! empty(config('spotify-client.client_id')) && ! empty(config('spotify-client.client_secret'));
    }

    private function setupLaravelApp(): int
    {
        info('🎸 Setting up Spotify for your Laravel application...');

        return $this->runCompleteSetup();
    }

    private function setupLaravelZeroApp(): int
    {
        info('⚡ Setting up Spotify for your Laravel Zero CLI app...');

        return $this->runCompleteSetup();
    }

    private function setupPackageDevelopment(): int
    {
        info('📦 Setting up Spotify for package development...');

        return $this->runCompleteSetup();
    }

    private function setupGenericPHPApp(): int
    {
        info('🐘 Setting up Spotify for your PHP application...');

        return $this->runCompleteSetup();
    }

    private function runCompleteSetup(): int
    {
        // Check for existing credentials first
        if ($this->checkExistingCredentials()) {
            return self::SUCCESS;
        }

        // Guide user through setup
        $setupChoice = select(
            'How would you like to set up Spotify credentials?',
            [
                'guided' => '🧭 Guided setup (recommended) - We\'ll walk you through everything',
                'manual' => '⚙️ Manual setup - I already have Spotify app credentials',
                'skip' => '⏭️ Skip setup - I\'ll configure later',
            ],
            'guided'
        );

        return match ($setupChoice) {
            'guided' => $this->runGuidedSetup(),
            'manual' => $this->runManualSetup(),
            'skip' => $this->skipSetup(),
            default => self::FAILURE,
        };
    }

    private function checkExistingCredentials(): bool
    {
        $clientId = env('SPOTIFY_CLIENT_ID');
        $clientSecret = env('SPOTIFY_CLIENT_SECRET');

        if ($clientId && $clientSecret) {
            info('✅ Found existing Spotify credentials');

            return spin(function () use ($clientId, $clientSecret) {
                return $this->validator->validateCredentials($clientId, $clientSecret);
            }, 'Validating credentials...');
        }

        return false;
    }

    private function runGuidedSetup(): int
    {
        info('🎯 Starting guided Spotify setup...');

        // Step 1: Create Spotify app
        $shouldCreateApp = confirm('Would you like us to help you create a Spotify app?', true);
        if ($shouldCreateApp) {
            $this->guideAppCreation();
        }

        // Step 2: Get credentials
        $credentials = $this->collectCredentials();
        if (! $credentials) {
            return self::FAILURE;
        }

        // Step 3: Configure authentication flow
        $authFlow = $this->selectAuthenticationFlow();

        // Step 4: Save configuration
        return $this->saveConfiguration($credentials, $authFlow);
    }

    private function runManualSetup(): int
    {
        info('⚙️ Manual credential setup...');

        $credentials = $this->collectCredentials();
        if (! $credentials) {
            return self::FAILURE;
        }

        $authFlow = $this->selectAuthenticationFlow();

        return $this->saveConfiguration($credentials, $authFlow);
    }

    private function skipSetup(): int
    {
        warning('⏭️ Skipping setup. You can run this command again anytime.');
        info('💡 To set up manually, add these to your .env file:');
        info('   SPOTIFY_CLIENT_ID=your_client_id');
        info('   SPOTIFY_CLIENT_SECRET=your_client_secret');

        return self::SUCCESS;
    }

    private function guideAppCreation(): void
    {
        info('📱 Let\'s create your Spotify app...');
        note('We\'ll open the Spotify Developer Dashboard for you.');

        $shouldOpen = confirm('Open Spotify Developer Dashboard?', true);
        if ($shouldOpen) {
            $this->appManager->openDeveloperDashboard();
        }

        info('📝 Follow these steps in the dashboard:');
        info('   1. Click "Create App"');
        info('   2. Fill in app name and description');
        info('   3. Set redirect URI to: http://localhost:8080/callback');
        info('   4. Copy your Client ID and Client Secret');
    }

    private function collectCredentials(): ?array
    {
        $clientId = text(
            label: 'Enter your Spotify Client ID',
            placeholder: 'e.g., 1234567890abcdef1234567890abcdef12345678',
            required: true,
            validate: fn ($value) => strlen($value) === 32 ? null : 'Client ID should be 32 characters long'
        );

        $clientSecret = text(
            label: 'Enter your Spotify Client Secret',
            placeholder: 'e.g., abcdef1234567890abcdef1234567890abcdef12',
            required: true,
            validate: fn ($value) => strlen($value) === 32 ? null : 'Client Secret should be 32 characters long'
        );

        // Validate credentials
        $isValid = spin(function () use ($clientId, $clientSecret) {
            return $this->validator->validateCredentials($clientId, $clientSecret);
        }, 'Validating credentials with Spotify...');

        if (! $isValid) {
            error('❌ Invalid credentials. Please check your Client ID and Secret.');

            return null;
        }

        info('✅ Credentials validated successfully!');

        return compact('clientId', 'clientSecret');
    }

    private function selectAuthenticationFlow(): string
    {
        return select(
            'Which authentication flow do you need?',
            [
                'client_credentials' => '🤖 Client Credentials (app-only access, no user login)',
                'authorization_code' => '👤 Authorization Code (user login required)',
            ],
            'client_credentials'
        );
    }

    private function saveConfiguration(array $credentials, string $authFlow): int
    {
        return spin(function () use ($credentials, $authFlow) {
            try {
                $this->environmentDetector->updateEnvironmentFile([
                    'SPOTIFY_CLIENT_ID' => $credentials['clientId'],
                    'SPOTIFY_CLIENT_SECRET' => $credentials['clientSecret'],
                    'SPOTIFY_AUTH_FLOW' => $authFlow,
                ]);

                info('✅ Configuration saved to .env file');

                return self::SUCCESS;
            } catch (\Exception $e) {
                error("Failed to save configuration: {$e->getMessage()}");

                return self::FAILURE;
            }
        }, 'Saving configuration...');
    }

    private function displayQuickStart(): void
    {
        info('🚀 Quick Start Examples:');

        $examples = $this->codeGenerator->generateQuickStartExamples();
        note($examples);

        info('📚 For more examples, check out the documentation!');
    }
}
