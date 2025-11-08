<?php

declare(strict_types=1);

namespace VigihDev\SymfonyBridge\Config;

use InvalidArgumentException;
use VigihDev\SymfonyBridge\Config\Contracts\ConfigBridgeContract;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Filesystem\Path;
use VigihDev\SymfonyBridge\Config\AttributeInjection\DependencyInjector;
use VigihDev\SymfonyBridge\Config\Service\ServiceLocator;

final class ConfigBridge implements ConfigBridgeContract
{
    private ContainerBuilder $container;
    private static bool $injectionEnabled = false;

    public static function boot(
        string $basePath,
        string $configDir = 'config',
        bool $enableAutoInjection = true,
        array $loadEnvPaths = []
    ): static {

        if (!is_dir($basePath) || !is_dir(Path::join($basePath, $configDir))) {
            throw new InvalidArgumentException("Directory {$basePath}/{$configDir} tidak tersedia.");
        }

        $bridge = new self($basePath);
        $bridge->loadEnv();
        // loadEnvPaths
        if (!empty($loadEnvPaths)) {
            foreach ($loadEnvPaths as $envPath) {
                if (!is_file($envPath)) {
                    throw new InvalidArgumentException("File {$envPath} tidak tersedia.");
                }
                $bridge->loadEnv($envPath);
            }
        }

        $bridge->loadConfig("{$basePath}/{$configDir}");
        $bridge->compile();


        // Enable dependency injection setelah container ready
        if ($enableAutoInjection) {
            self::$injectionEnabled = true;
            DependencyInjector::setContainer($bridge->container);
        }

        return $bridge;
    }


    public function __construct(
        private readonly string $basePath
    ) {
        $this->container = new ContainerBuilder();
    }

    public function loadEnv(?string $path = null): void
    {
        $dotenv = new Dotenv();
        $envPath = $path ?? "{$this->basePath}/.env";
        if (is_file($envPath)) {
            $dotenv->usePutenv(true)->loadEnv($envPath);
        }
    }

    public function loadConfig(string $configDir): void
    {
        $loader = new YamlFileLoader($this->container, new FileLocator($configDir));
        foreach (glob("{$configDir}/*.yaml") as $file) {
            $loader->load(basename($file));
        }
    }

    public function addConfiguration(ConfigurationInterface $configuration, ?string $id = null): void
    {
        $id ??= get_class($configuration);

        $definition = new Definition(get_class($configuration));
        $definition->setPublic(true);

        // Optional: tambahkan tag untuk grouping atau debugging
        $definition->addTag('vigihdev.config');
        $this->container->setDefinition($id, $definition);
    }

    public function compile(): ContainerBuilder
    {
        $this->container->compile(true);
        ServiceLocator::setContainer($this->container);
        return $this->container;
    }

    public function get(string $id): ?object
    {
        return $this->container->has($id) ? $this->container->get($id) : null;
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    public function container(): ContainerBuilder
    {
        return $this->container;
    }

    /**
     * Create service with auto dependency injection
     */
    public static function make(string $className): object
    {
        if (!self::$injectionEnabled) {
            throw new \RuntimeException('Auto injection belum di-enable. Panggil ConfigBridge::boot() terlebih dahulu.');
        }

        $instance = new $className();
        DependencyInjector::inject($instance);
        return $instance;
    }

    /**
     * Check if auto injection is enabled
     */
    public static function isInjectionEnabled(): bool
    {
        return self::$injectionEnabled;
    }
}
