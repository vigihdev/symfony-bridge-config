# Symfony Bridge Config

A lightweight configuration bridge for Symfony components that provides easy environment and YAML configuration management with dependency injection container support.

## Installation

```bash
composer require vigihdev/symfony-bridge-config
```

## Quick Start

### Simple Usage with Boot Method

```php
use VigihDev\SymfonyBridge\Config\ConfigBridge;
use VigihDev\SymfonyBridge\Config\Service\ServiceLocator;

require __DIR__ . '/vendor/autoload.php';

// Boot with automatic configuration loading
$config = ConfigBridge::boot(__DIR__);
$container = $config->container();

// Access parameters
echo $config->container()->getParameter('app.env');
echo ServiceLocator::getParameter('app.env');
```

### Manual Configuration

```php
use VigihDev\SymfonyBridge\Config\ConfigBridge;
use App\Config\AppConfig;

$config = new ConfigBridge(__DIR__);
$config->loadEnv();                           // Load .env file
$config->loadConfig(__DIR__ . '/config');    // Load YAML configs
$config->addConfiguration(new AppConfig());  // Add custom config
$config->compile();                          // Compile container

// Use the container
$appConfig = $config->get(AppConfig::class);
```

## Features

- **Environment Loading**: Automatic `.env` file loading with `putenv()` support
- **YAML Configuration**: Load multiple YAML configuration files
- **Custom Configurations**: Add Symfony Configuration classes
- **Service Locator**: Static access to container services and parameters
- **Dependency Injection**: Full Symfony DI container support

## Configuration Structure

```
project/
├── .env                    # Environment variables
├── config/
│   ├── services.yaml      # Service definitions
│   └── packages/
│       └── *.yaml         # Package configurations
└── src/
    └── Config/
        └── AppConfig.php  # Custom configuration classes
```

## Environment Variables

Create a `.env` file in your project root:

```env
APP_ENV=production
APP_DEBUG=false
DATABASE_URL=mysql://user:pass@localhost/dbname
```

## YAML Configuration

Create `config/services.yaml`:

```yaml
parameters:
  app.env: '%env(APP_ENV)%'
  app.debug: '%env(bool:APP_DEBUG)%'
  database.url: '%env(DATABASE_URL)%'

services:
  App\Service\MyService:
    arguments:
      - '%app.env%'
```

## Custom Configuration Classes

```php
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class AppConfig implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('app');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('env')->defaultValue('dev')->end()
                ->scalarNode('debug')->defaultTrue()->end()
                ->arrayNode('database')
                    ->children()
                        ->scalarNode('host')->defaultValue('localhost')->end()
                        ->scalarNode('port')->defaultValue('3306')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
```

## API Reference

### ConfigBridge

#### Static Methods

- `ConfigBridge::boot(string $basePath, string $configDir = 'config'): static`
  - Bootstrap with automatic configuration loading

#### Instance Methods

- `__construct(string $basePath)`
- `loadEnv(?string $path = null): void`
- `loadConfig(string $configDir): void`
- `addConfiguration(ConfigurationInterface $config, ?string $id = null): void`
- `compile(): ContainerBuilder`
- `get(string $id): ?object`
- `has(string $id): bool`
- `container(): ContainerBuilder`

### ServiceLocator

#### Static Methods

- `ServiceLocator::setContainer(ContainerInterface $container): void`
- `ServiceLocator::getContainer(): ContainerInterface`
- `ServiceLocator::get(string $id): object`
- `ServiceLocator::getParameter(string $name): mixed`
- `ServiceLocator::hasParameter(string $name): bool`
- `ServiceLocator::has(string $id): bool`

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit
```

Test coverage includes:
- Environment variable loading
- YAML configuration parsing
- Service container management
- Configuration object registration
- ServiceLocator functionality
- Integration testing

## Requirements

- PHP 8.1+
- Symfony Config Component
- Symfony DependencyInjection Component
- Symfony Dotenv Component

## License

This project is licensed under the MIT License.
