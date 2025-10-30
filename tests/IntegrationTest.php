<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use VigihDev\SymfonyBridge\Config\ConfigBridge;
use VigihDev\SymfonyBridge\Config\Service\ServiceLocator;
use Tests\Entity\AppConfig;

class IntegrationTest extends TestCase
{
    private string $testDir;
    private string $configDir;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/integration_test_' . uniqid();
        $this->configDir = $this->testDir . '/config';
        
        mkdir($this->testDir, 0777, true);
        mkdir($this->configDir, 0777, true);
        
        file_put_contents($this->testDir . '/.env', "APP_ENV=production\nAPP_DEBUG=false");
        file_put_contents($this->configDir . '/services.yaml', 
            "parameters:\n  app.env: '%env(APP_ENV)%'\n  app.debug: '%env(bool:APP_DEBUG)%'"
        );
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->testDir);
        
        // Reset ServiceLocator
        $reflection = new \ReflectionClass(ServiceLocator::class);
        $property = $reflection->getProperty('container');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    public function testFullIntegrationWithBoot(): void
    {
        $bridge = ConfigBridge::boot($this->testDir);
        
        // Test container parameters
        $this->assertEquals('production', $bridge->container()->getParameter('app.env'));
        $this->assertFalse($bridge->container()->getParameter('app.debug'));
        
        // Test ServiceLocator integration
        $this->assertEquals('production', ServiceLocator::getParameter('app.env'));
        $this->assertFalse(ServiceLocator::getParameter('app.debug'));
    }

    public function testManualConfigurationFlow(): void
    {
        $bridge = new ConfigBridge($this->testDir);
        $bridge->loadEnv();
        $bridge->loadConfig($this->configDir);
        $bridge->addConfiguration(new AppConfig());
        $bridge->compile();
        
        // Test environment variables
        $this->assertEquals('production', getenv('APP_ENV'));
        $this->assertEquals('false', getenv('APP_DEBUG'));
        
        // Test configuration object
        $appConfig = $bridge->get(AppConfig::class);
        $this->assertInstanceOf(AppConfig::class, $appConfig);
        
        // Test ServiceLocator functionality
        $this->assertTrue(ServiceLocator::has(AppConfig::class));
        $this->assertInstanceOf(AppConfig::class, ServiceLocator::get(AppConfig::class));
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
