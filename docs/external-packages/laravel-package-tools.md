# Spatie Laravel Package Tools

This document provides comprehensive information about using Spatie Laravel Package Tools for package development.

## Overview

Laravel Package Tools simplifies service providers in Laravel packages by providing a fluent, standardized approach to configuring package components.

## Basic Setup

### Service Provider Structure
```php
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class YourPackageServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('your-package-name')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations(['create_your_table'])
            ->hasRoutes(['web', 'api'])
            ->hasCommands([YourCommand::class])
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations();
            });
    }
}
```

## Available Package Components

### Configuration Files
```php
$package->hasConfigFile();                    // Expects config/package-name.php
$package->hasConfigFile('custom-config');     // Expects config/custom-config.php
```

### Views
```php
$package->hasViews();                         // Views in resources/views/
$package->hasViewComponents('package-name');  // Blade components
```

### Migrations
```php
$package->hasMigrations(['migration_name']);  // Without timestamp
$package->hasMigrations(['2023_01_01_000000_create_table']);  // With timestamp
```

### Routes
```php
$package->hasRoutes(['web']);                 // Single route file
$package->hasRoutes(['web', 'api']);         // Multiple route files
```

### Commands
```php
$package->hasCommands([YourCommand::class]);
```

### Assets
```php
$package->hasAssets();                        // CSS/JS files in resources/dist/
```

### Translations
```php
$package->hasTranslations();                  // Language files in resources/lang/
```

## Install Command Features

The install command provides a comprehensive setup experience:

### Basic Install Command
```php
$package->hasInstallCommand(function(InstallCommand $command) {
    $command
        ->publishConfigFile()
        ->publishAssets()
        ->publishMigrations()
        ->askToRunMigrations()
        ->askToStarRepoOnGitHub('vendor/repo-name');
});
```

### Available Install Command Methods

#### Publishing Resources
```php
$command->publishConfigFile();        // Publish config file
$command->publishAssets();           // Publish CSS/JS assets
$command->publishMigrations();       // Publish migration files
$command->publishInertiaComponents(); // Publish Inertia components
$command->publish('custom-tag');     // Publish custom tagged resources
```

#### Interactive Features
```php
$command->askToRunMigrations();      // Prompt to run migrations
$command->askToStarRepoOnGitHub('vendor/repo');  // Ask to star repository
```

#### Service Provider Management
```php
$command->copyAndRegisterServiceProviderInApp();  // Add to config/app.php
```

#### Custom Logic
```php
$command->startWith(function(InstallCommand $command) {
    // Custom logic before installation
    $command->info('Starting installation...');
});

$command->endWith(function(InstallCommand $command) {
    // Custom logic after installation
    $command->info('Installation complete!');
});
```

## Advanced Features

### Custom Commands
Create commands that extend Laravel's base Command class:

```php
use Illuminate\Console\Command;

class YourCustomCommand extends Command
{
    protected $signature = 'your-package:command';
    protected $description = 'Description of your command';

    public function handle()
    {
        $this->info('Command executed!');
    }
}
```

### View Composers
```php
$package->hasViewComposer('view-name', ViewComposer::class);
```

### Service Providers
```php
$package->hasServiceProviders([AnotherServiceProvider::class]);
```

## Directory Structure

Expected package structure:
```
your-package/
├── config/
│   └── package-name.php
├── database/
│   └── migrations/
├── resources/
│   ├── dist/           # Assets
│   ├── lang/           # Translations
│   └── views/          # Views
├── routes/
│   ├── web.php
│   └── api.php
├── src/
│   ├── Commands/
│   ├── YourPackageServiceProvider.php
│   └── ...
└── tests/
```

## Install Command Workflow

The install command follows this workflow:
1. `processStartWith()` - Execute custom start logic
2. `processPublishes()` - Publish tagged resources
3. `processAskToRunMigrations()` - Prompt for migrations
4. `processCopyServiceProviderInApp()` - Register service provider
5. `processStarRepo()` - Ask to star repository
6. `processEndWith()` - Execute custom end logic

## Best Practices

### Package Naming
- Use kebab-case for package names
- Keep names descriptive but concise
- Follow vendor/package-name convention

### Resource Publishing
- Use descriptive tag names for publishing
- Group related resources with same tags
- Provide sensible defaults in config files

### Install Commands
- Keep install process simple and guided
- Provide clear feedback during installation
- Test for existing configurations before overwriting

### Error Handling
```php
$command->startWith(function(InstallCommand $command) {
    if (!extension_loaded('curl')) {
        $command->error('cURL extension is required');
        return false;
    }
});
```

## Laravel Prompts Integration

Since Laravel Prompts are available, you can create rich interactive experiences:

```php
use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;

$command->startWith(function(InstallCommand $command) {
    $environment = select(
        'Which environment are you setting up?',
        ['development', 'staging', 'production']
    );

    $apiKey = text(
        'Enter your API key:',
        required: true
    );

    if (confirm('Do you want to publish the config file?')) {
        // Custom publishing logic
    }
});
```

## Custom Install Command

For complex setups, create a custom install command:

```php
use Spatie\LaravelPackageTools\Commands\InstallCommand;

class CustomInstallCommand extends InstallCommand
{
    public function handle()
    {
        $this->info('Starting custom installation...');
        
        // Custom installation logic
        $this->setupEnvironment();
        $this->configureServices();
        $this->runTests();
        
        parent::handle(); // Run standard install process
        
        $this->info('Custom installation complete!');
    }

    private function setupEnvironment()
    {
        // Custom environment setup
    }
}
```

Then register it:
```php
$package->hasInstallCommand(function($installCommand) {
    return new CustomInstallCommand($this);
});
```