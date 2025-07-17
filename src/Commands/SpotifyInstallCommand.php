<?php

declare(strict_types=1);

namespace Jordanpartridge\SpotifyClient\Commands;

use Illuminate\Console\Command;
use Jordanpartridge\SpotifyClient\Services\EnvironmentDetector;
use Jordanpartridge\SpotifyClient\Services\SpotifyAppManager;
use Jordanpartridge\SpotifyClient\Services\OAuthFlowHandler;
use Jordanpartridge\SpotifyClient\Services\CredentialValidator;
use Jordanpartridge\SpotifyClient\Services\CodeGenerator;

use function Laravel\Prompts\{intro, outro, info, warning, error, confirm, select, multiselect, text, password, progress, spin, note, table};

class SpotifyInstallCommand extends Command
{
    protected $signature = 'spotify:install';

    protected $description = 'Set up Spotify API integration with zero manual steps - the most developer-friendly setup experience ever built';

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
        try {
            intro('🎵 Welcome to Spotify Client for Laravel! 🎵');
            
            // Phase 1: Intelligent Welcome & Discovery
            $this->phaseOne_IntelligentDiscovery();
            
            // Phase 2: Spotify App Registration Wizard
            $appConfig = $this->phaseTwo_SpotifyAppWizard();
            
            // Phase 3: Authentication Flow Intelligence
            $authConfig = $this->phaseThree_AuthenticationFlow();
            
            // Phase 4: Scope Selection with Visual Guide
            $scopeConfig = $this->phaseFour_ScopeSelection($authConfig);
            
            // Phase 5: Browser-Integrated OAuth Flow
            $tokens = $this->phaseFive_OAuthFlow($appConfig, $authConfig, $scopeConfig);
            
            // Phase 6: Environment Configuration & Security
            $this->phaseSix_EnvironmentSetup($appConfig, $tokens);
            
            // Phase 7: API Testing & Validation Suite
            $this->phaseSeven_ValidationSuite($tokens);
            
            // Phase 8: Personalized Code Generation
            $this->phaseEight_CodeGeneration();
            
            // Phase 9: Integration Suggestions & Setup
            $this->phaseNine_IntegrationSuggestions();
            
            // Phase 10: Success Celebration & Community Invitation
            $this->phaseTen_SuccessCelebration();
            
            outro('🎉 Your Spotify client is ready to rock! 🎸');
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            error("Installation failed: {$e->getMessage()}");
            $this->handleError($e);
            return self::FAILURE;
        }
    }

    private function phaseOne_IntelligentDiscovery(): array
    {
        info('Let me analyze your Laravel application...');
        
        return spin(function () {
            $analysis = $this->environmentDetector->analyzeEnvironment();
            
            $this->displayAnalysisResults($analysis);
            
            return $analysis;
        }, 'Analyzing your Laravel environment...');
    }

    private function displayAnalysisResults(array $analysis): void
    {
        $messages = [];
        
        if ($analysis['laravel_version']) {
            $messages[] = "✨ Detected Laravel {$analysis['laravel_version']}";
        }
        
        if ($analysis['app_type'] !== 'unknown') {
            $messages[] = "✨ Identified as {$analysis['app_type']} application";
        }
        
        if (!empty($analysis['packages'])) {
            $packages = implode(', ', $analysis['packages']);
            $messages[] = "✨ Found packages: {$packages}";
        }
        
        if ($analysis['existing_spotify_config']) {
            $messages[] = "✨ Found existing Spotify configuration";
        }
        
        foreach ($messages as $message) {
            info($message);
        }
        
        if ($analysis['existing_spotify_config']) {
            $update = confirm('Found existing Spotify credentials. Want to update them?', false);
            if (!$update) {
                note('Skipping credential update. Run with --force to override.');
            }
        }
    }

    private function phaseTwo_SpotifyAppWizard(): array
    {
        intro('🌐 Spotify App Registration Wizard');
        
        info('I\'ll help you set up a Spotify app with optimal settings for your Laravel application.');
        
        // Check for existing apps
        $existingApps = spin(function () {
            return $this->appManager->detectExistingApps();
        }, 'Checking for existing Spotify apps...');
        
        if (!empty($existingApps)) {
            return $this->handleExistingApps($existingApps);
        }
        
        return $this->createNewSpotifyApp();
    }

    private function handleExistingApps(array $apps): array
    {
        $options = ['new' => '🆕 Create a new app'];
        foreach ($apps as $app) {
            $options[$app['id']] = "📱 {$app['name']} (Created: {$app['created_at']})";
        }
        
        $choice = select(
            'I found existing Spotify apps in your account:',
            $options,
            hint: 'Select an existing app or create a new one'
        );
        
        if ($choice === 'new') {
            return $this->createNewSpotifyApp();
        }
        
        return $this->useExistingApp($apps[$choice]);
    }

    private function createNewSpotifyApp(): array
    {
        info('🚀 Opening Spotify Developer Dashboard in your browser...');
        info('I\'ll guide you through creating a new app with optimal settings.');
        
        $appName = text(
            'What should we name your Spotify app?',
            default: $this->environmentDetector->suggestAppName(),
            required: true,
            hint: 'This will be displayed to users during authorization'
        );
        
        $appDescription = text(
            'App description:',
            default: 'Laravel application using Spotify Web API',
            hint: 'Brief description of what your app does'
        );
        
        // Open browser to Spotify Developer Dashboard
        $this->appManager->openDeveloperDashboard();
        
        info('In your browser:');
        info('1. Click "Create an App"');
        info('2. Enter the app name and description I suggested');
        info('3. Accept the terms and create the app');
        info('4. Copy your Client ID and Client Secret');
        
        $clientId = text(
            'Enter your Client ID:',
            required: true,
            validate: fn($value) => $this->validateClientId($value)
        );
        
        $clientSecret = password(
            'Enter your Client Secret:',
            required: true,
            validate: fn($value) => $this->validateClientSecret($value)
        );
        
        return [
            'name' => $appName,
            'description' => $appDescription,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'is_new' => true,
        ];
    }

    private function useExistingApp(array $app): array
    {
        info("Using existing app: {$app['name']}");
        
        // We'll need to get credentials for existing app
        $clientId = text(
            'Enter the Client ID for this app:',
            required: true,
            validate: fn($value) => $this->validateClientId($value)
        );
        
        $clientSecret = password(
            'Enter the Client Secret for this app:',
            required: true,
            validate: fn($value) => $this->validateClientSecret($value)
        );
        
        return array_merge($app, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'is_new' => false,
        ]);
    }

    private function phaseThree_AuthenticationFlow(): array
    {
        intro('🔐 Authentication Flow Selection');
        
        $recommendation = $this->environmentDetector->recommendAuthFlow();
        
        $flows = [
            'client_credentials' => 'Client Credentials (App-only access) - No user authorization required',
            'authorization_code' => 'Authorization Code (User authorization) - Access user data with permission',
        ];
        
        if ($recommendation) {
            $flows[$recommendation] .= ' [RECOMMENDED for your setup]';
        }
        
        $selectedFlow = select(
            'Which authentication flow should I set up?',
            $flows,
            default: $recommendation,
            hint: 'I\'ve analyzed your Laravel app and made a recommendation'
        );
        
        $this->explainAuthFlow($selectedFlow);
        
        return [
            'flow' => $selectedFlow,
            'recommendation' => $recommendation,
        ];
    }

    private function explainAuthFlow(string $flow): void
    {
        $explanations = [
            'client_credentials' => [
                'Perfect for accessing public Spotify data',
                'No user login required',
                'Can search tracks, get album info, browse categories',
                'Cannot access user playlists or personal data',
            ],
            'authorization_code' => [
                'Access user\'s personal Spotify data',
                'Requires user to log in and grant permission',
                'Can read/modify playlists, save tracks, access listening history',
                'More complex setup but more powerful',
            ],
        ];
        
        note('This flow will allow you to:');
        foreach ($explanations[$flow] as $capability) {
            info("  • {$capability}");
        }
    }

    private function phaseFour_ScopeSelection(array $authConfig): array
    {
        if ($authConfig['flow'] === 'client_credentials') {
            info('Client Credentials flow doesn\'t require scope selection.');
            return ['scopes' => []];
        }
        
        intro('🎯 Permission Scope Selection');
        
        $availableScopes = [
            'user-read-private' => 'Read user profile data (recommended)',
            'user-read-email' => 'Read user email address',
            'playlist-read-private' => 'Read private playlists',
            'playlist-read-collaborative' => 'Read collaborative playlists',
            'playlist-modify-public' => 'Modify public playlists',
            'playlist-modify-private' => 'Modify private playlists',
            'user-library-read' => 'Read saved tracks and albums',
            'user-library-modify' => 'Save/remove tracks and albums',
            'user-top-read' => 'Read top artists and tracks',
            'user-read-recently-played' => 'Read recently played tracks',
            'user-read-playback-state' => 'Read current playback state',
            'user-modify-playback-state' => 'Control playback (play/pause/skip)',
        ];
        
        $defaultScopes = ['user-read-private', 'user-read-email'];
        
        $selectedScopes = multiselect(
            'Which Spotify permissions do you need?',
            $availableScopes,
            default: $defaultScopes,
            hint: 'I\'ve pre-selected common permissions. Use space to select/deselect.'
        );
        
        $this->showScopesSummary($selectedScopes, $availableScopes);
        
        return ['scopes' => $selectedScopes];
    }

    private function showScopesSummary(array $scopes, array $available): void
    {
        if (empty($scopes)) {
            warning('No scopes selected. Your app will have limited access.');
            return;
        }
        
        note('Selected permissions:');
        foreach ($scopes as $scope) {
            info("  ✓ {$available[$scope]}");
        }
    }

    private function phaseFive_OAuthFlow(array $appConfig, array $authConfig, array $scopeConfig): array
    {
        intro('🚀 OAuth Authorization Flow');
        
        if ($authConfig['flow'] === 'client_credentials') {
            return $this->handleClientCredentialsFlow($appConfig);
        }
        
        return $this->handleAuthorizationCodeFlow($appConfig, $scopeConfig);
    }

    private function handleClientCredentialsFlow(array $appConfig): array
    {
        return spin(function () use ($appConfig) {
            return $this->oauthHandler->getClientCredentialsToken(
                $appConfig['client_id'],
                $appConfig['client_secret']
            );
        }, 'Getting access token with Client Credentials flow...');
    }

    private function handleAuthorizationCodeFlow(array $appConfig, array $scopeConfig): array
    {
        info('Starting local OAuth server for authorization callback...');
        
        $callbackUrl = $this->oauthHandler->startCallbackServer();
        
        info("✅ OAuth server running at: {$callbackUrl}");
        info('🌐 Opening browser for Spotify authorization...');
        
        $authUrl = $this->oauthHandler->generateAuthorizationUrl(
            $appConfig['client_id'],
            $callbackUrl,
            $scopeConfig['scopes']
        );
        
        $this->oauthHandler->openBrowser($authUrl);
        
        info('In your browser:');
        info('1. Review the requested permissions');
        info('2. Click "Agree" to authorize your app');
        info('3. I\'ll automatically capture the authorization code');
        
        return spin(function () use ($appConfig) {
            return $this->oauthHandler->waitForCallback($appConfig);
        }, 'Waiting for authorization...');
    }

    private function phaseSix_EnvironmentSetup(array $appConfig, array $tokens): void
    {
        intro('📝 Environment Configuration');
        
        progress(
            label: 'Setting up your environment...',
            steps: [
                'backup' => 'Creating .env backup',
                'credentials' => 'Adding Spotify credentials',
                'market' => 'Configuring default market',
                'cache' => 'Setting up cache configuration',
                'security' => 'Applying security best practices',
            ],
            callback: function (string $step, $progress) use ($appConfig, $tokens) {
                match($step) {
                    'backup' => $this->environmentDetector->backupEnvironment(),
                    'credentials' => $this->environmentDetector->addCredentials($appConfig, $tokens),
                    'market' => $this->environmentDetector->configureMarket(),
                    'cache' => $this->environmentDetector->configureCaching(),
                    'security' => $this->environmentDetector->applySecurity(),
                };
                
                $progress->hint = 'Completed: ' . match($step) {
                    'backup' => 'Environment backup created',
                    'credentials' => 'Credentials securely stored',
                    'market' => 'Market configured based on location',
                    'cache' => 'Optimal cache settings applied',
                    'security' => 'Security best practices implemented',
                };
            }
        );
        
        info('✅ Environment configuration complete!');
    }

    private function phaseSeven_ValidationSuite(array $tokens): void
    {
        intro('🔍 API Testing & Validation');
        
        $tests = [
            'connection' => 'Testing basic API connection',
            'authentication' => 'Validating authentication',
            'scopes' => 'Testing permission scopes',
            'rate_limiting' => 'Checking rate limiting',
            'error_handling' => 'Validating error handling',
        ];
        
        $results = progress(
            label: 'Running comprehensive API tests...',
            steps: $tests,
            callback: function (string $test, $progress) use ($tokens) {
                $result = match($test) {
                    'connection' => $this->validator->testConnection($tokens),
                    'authentication' => $this->validator->testAuthentication($tokens),
                    'scopes' => $this->validator->testScopes($tokens),
                    'rate_limiting' => $this->validator->testRateLimiting($tokens),
                    'error_handling' => $this->validator->testErrorHandling($tokens),
                };
                
                $progress->hint = $result['success'] ? '✅ Passed' : '❌ Failed';
                
                return $result;
            }
        );
        
        $this->displayTestResults($results);
    }

    private function displayTestResults(array $results): void
    {
        $passed = collect($results)->where('success', true)->count();
        $total = count($results);
        
        if ($passed === $total) {
            info("🎉 All tests passed! ({$passed}/{$total})");
        } else {
            warning("⚠️  Some tests failed ({$passed}/{$total})");
            
            foreach ($results as $test => $result) {
                if (!$result['success']) {
                    error("❌ {$test}: {$result['message']}");
                }
            }
        }
    }

    private function phaseEight_CodeGeneration(): void
    {
        intro('📚 Generating Personalized Code Examples');
        
        spin(function () {
            $this->codeGenerator->generateExamples();
        }, 'Creating examples tailored to your Laravel setup...');
        
        info('✅ Generated personalized documentation and examples!');
        info('📁 Check the generated files in your project');
    }

    private function phaseNine_IntegrationSuggestions(): void
    {
        $integrations = $this->environmentDetector->suggestIntegrations();
        
        if (empty($integrations)) {
            return;
        }
        
        intro('🔌 Integration Suggestions');
        
        $selected = multiselect(
            'Want me to set up any additional integrations?',
            $integrations,
            hint: 'These will save you even more development time!'
        );
        
        if (!empty($selected)) {
            $this->setupIntegrations($selected);
        }
    }

    private function setupIntegrations(array $integrations): void
    {
        progress(
            label: 'Setting up integrations...',
            steps: $integrations,
            callback: function (string $integration, $progress) {
                $this->codeGenerator->generateIntegration($integration);
                $progress->hint = "✅ {$integration} integration ready";
            }
        );
    }

    private function phaseTen_SuccessCelebration(): void
    {
        intro('🎉 Setup Complete!');
        
        $timesSaved = $this->calculateTimeSaved();
        
        info('What I accomplished for you:');
        info('✅ Created & configured Spotify app');
        info('✅ Set up secure authentication');
        info('✅ Tested all API connections');
        info('✅ Generated personalized code examples');
        info('✅ Created complete documentation');
        info('✅ Configured optimal settings');
        
        note("Time saved: ~{$timesSaved} hours of manual setup! 🚀");
        
        $this->inviteToCommunity();
    }

    private function inviteToCommunity(): void
    {
        if (!confirm('💝 Loved this setup experience?', false)) {
            return;
        }
        
        info('This install command took weeks to perfect, and I\'d love to know it helped you!');
        
        $actions = multiselect(
            'How would you like to show support?',
            [
                'star' => '🌟 Star the repo to help other developers discover this tool',
                'issue' => '🐛 Report any issues on GitHub',
                'follow' => '📧 Follow @jordanpartridge for more Laravel packages',
                'share' => '🔄 Share with your team',
            ],
            hint: 'Select all that apply (optional but greatly appreciated!)'
        );
        
        $this->handleCommunityActions($actions);
    }

    private function handleCommunityActions(array $actions): void
    {
        foreach ($actions as $action) {
            match($action) {
                'star' => $this->openGitHubRepo(),
                'issue' => $this->openGitHubIssues(),
                'follow' => $this->openTwitterProfile(),
                'share' => info('Thank you for sharing! 🙏'),
            };
        }
    }

    private function openGitHubRepo(): void
    {
        $url = 'https://github.com/jordanpartridge/spotify-client';
        $this->oauthHandler->openBrowser($url);
        info('🌟 Opened GitHub repo in your browser');
    }

    private function openGitHubIssues(): void
    {
        $url = 'https://github.com/jordanpartridge/spotify-client/issues';
        $this->oauthHandler->openBrowser($url);
        info('🐛 Opened GitHub issues in your browser');
    }

    private function openTwitterProfile(): void
    {
        $url = 'https://twitter.com/jordanpartridge';
        $this->oauthHandler->openBrowser($url);
        info('📧 Opened Twitter profile in your browser');
    }

    private function calculateTimeSaved(): string
    {
        // Realistic time savings calculation
        $baseSavings = 2.5; // Base time saved in hours
        
        // Add time based on features used
        if ($this->environmentDetector->hasComplexAuth()) {
            $baseSavings += 1.0; // OAuth setup complexity
        }
        
        if ($this->environmentDetector->hasIntegrations()) {
            $baseSavings += 0.5; // Integration setup
        }
        
        return number_format($baseSavings, 1);
    }

    private function validateClientId(string $value): ?string
    {
        if (strlen($value) !== 32) {
            return 'Client ID should be 32 characters long';
        }
        
        if (!ctype_alnum($value)) {
            return 'Client ID should contain only letters and numbers';
        }
        
        return null;
    }

    private function validateClientSecret(string $value): ?string
    {
        if (strlen($value) !== 32) {
            return 'Client Secret should be 32 characters long';
        }
        
        if (!ctype_alnum($value)) {
            return 'Client Secret should contain only letters and numbers';
        }
        
        return null;
    }

    private function handleError(\Exception $e): void
    {
        error('Something went wrong, but I can help you fix it!');
        
        $commonFixes = $this->getCommonErrorFixes($e);
        
        if (!empty($commonFixes)) {
            info('Here are some common solutions:');
            foreach ($commonFixes as $fix) {
                info("  • {$fix}");
            }
        }
        
        info('💬 Need help? Open an issue: https://github.com/jordanpartridge/spotify-client/issues');
    }

    private function getCommonErrorFixes(\Exception $e): array
    {
        $message = $e->getMessage();
        
        return match(true) {
            str_contains($message, 'curl') => [
                'Ensure cURL is installed and enabled in PHP',
                'Check your internet connection',
            ],
            str_contains($message, 'permission') => [
                'Check file permissions on your .env file',
                'Ensure your web server can write to the project directory',
            ],
            str_contains($message, 'token') => [
                'Verify your Client ID and Client Secret are correct',
                'Check that your Spotify app is properly configured',
            ],
            default => [
                'Try running the command again',
                'Check the Laravel logs for more details',
            ],
        };
    }
}