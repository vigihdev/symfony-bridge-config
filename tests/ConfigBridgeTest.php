<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use VigihDev\SymfonyBridge\Config\ConfigBridge;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use InvalidArgumentException;
use RuntimeException;

class ConfigBridgeTest extends TestCase
{
    private string $testDir;
    private string $configDir;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/config_bridge_test_' . uniqid();
        $this->configDir = $this->testDir . '/config';
        
        mkdir($this->testDir, 0777, true);
        mkdir($this->configDir, 0777, true);
        
        // Create test .env file
        file_put_contents($this->testDir . '/.env', "APP_ENV=test\nAPP_DEBUG=true");
        
        // Create test config file
        file_put_contents($this->configDir . '/services.yaml', "parameters:\n  app.env: '%env(APP_ENV)%'");
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->testDir);
    }

    public function testBootWithValidDirectory(): void
    {
        $bridge = ConfigBridge::boot($this->testDir);
        
        $this->assertInstanceOf(ConfigBridge::class, $bridge);
        $this->assertTrue($bridge->container()->hasParameter('app.env'));
        $this->assertEquals('test', $bridge->container()->getParameter('app.env'));
    }

    public function testBootWithInvalidDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory /invalid/path/config tidak tersedia.');
        
        ConfigBridge::boot('/invalid/path');
    }

    public function testConstructor(): void
    {
        $bridge = new ConfigBridge($this->testDir);
        
        $this->assertInstanceOf(ConfigBridge::class, $bridge);
    }

    public function testLoadEnvWithDefaultPath(): void
    {
        $bridge = new ConfigBridge($this->testDir);
        $bridge->loadEnv();
        
        $this->assertEquals('test', getenv('APP_ENV'));
    }

    public function testLoadEnvWithCustomPath(): void
    {
        $customEnvPath = $this->testDir . '/custom.env';
        file_put_contents($customEnvPath, "CUSTOM_VAR=custom_value");
        
        $bridge = new ConfigBridge($this->testDir);
        $bridge->loadEnv($customEnvPath);
        
        $this->assertEquals('custom_value', getenv('CUSTOM_VAR'));
    }

    public function testLoadConfig(): void
    {
        $bridge = new ConfigBridge($this->testDir);
        $bridge->loadEnv();
        $bridge->loadConfig($this->configDir);
        $bridge->compile();
        
        $this->assertTrue($bridge->container()->hasParameter('app.env'));
    }

    public function testAddConfiguration(): void
    {
        $bridge = new ConfigBridge($this->testDir);
        $config = new TestConfiguration();
        
        $bridge->addConfiguration($config);
        $bridge->compile();
        
        $this->assertTrue($bridge->has(TestConfiguration::class));
    }

    public function testAddConfigurationWithCustomId(): void
    {
        $bridge = new ConfigBridge($this->testDir);
        $config = new TestConfiguration();
        
        $bridge->addConfiguration($config, 'custom.config');
        $bridge->compile();
        
        $this->assertTrue($bridge->has('custom.config'));
    }

    public function testGet(): void
    {
        $bridge = new ConfigBridge($this->testDir);
        $config = new TestConfiguration();
        
        $bridge->addConfiguration($config);
        $bridge->compile();
        
        $result = $bridge->get(TestConfiguration::class);
        $this->assertInstanceOf(TestConfiguration::class, $result);
    }

    public function testGetNonExistent(): void
    {
        $bridge = new ConfigBridge($this->testDir);
        $bridge->compile();
        
        $result = $bridge->get('non.existent');
        $this->assertNull($result);
    }

    public function testHas(): void
    {
        $bridge = new ConfigBridge($this->testDir);
        $config = new TestConfiguration();
        
        $bridge->addConfiguration($config);
        $bridge->compile();
        
        $this->assertTrue($bridge->has(TestConfiguration::class));
        $this->assertFalse($bridge->has('non.existent'));
    }

    public function testContainer(): void
    {
        $bridge = new ConfigBridge($this->testDir);
        
        $container = $bridge->container();
        $this->assertInstanceOf(\Symfony\Component\DependencyInjection\ContainerBuilder::class, $container);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

class TestConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): \Symfony\Component\Config\Definition\Builder\TreeBuilder
    {
        return new \Symfony\Component\Config\Definition\Builder\TreeBuilder('test');
    }
}
