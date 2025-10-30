<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use VigihDev\SymfonyBridge\Config\Service\ServiceLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use RuntimeException;

class ServiceLocatorTest extends TestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('test.param', 'test_value');
        $this->container->register('test.service', \stdClass::class)->setPublic(true);
        $this->container->compile();
    }

    protected function tearDown(): void
    {
        // Reset static container
        $reflection = new \ReflectionClass(ServiceLocator::class);
        $property = $reflection->getProperty('container');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    public function testSetContainer(): void
    {
        ServiceLocator::setContainer($this->container);
        
        $this->assertSame($this->container, ServiceLocator::getContainer());
    }

    public function testGetContainerWithoutSetting(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Container has not been set yet');
        
        ServiceLocator::getContainer();
    }

    public function testGet(): void
    {
        ServiceLocator::setContainer($this->container);
        
        $service = ServiceLocator::get('test.service');
        $this->assertInstanceOf(\stdClass::class, $service);
    }

    public function testGetParameter(): void
    {
        ServiceLocator::setContainer($this->container);
        
        $param = ServiceLocator::getParameter('test.param');
        $this->assertEquals('test_value', $param);
    }

    public function testHasParameter(): void
    {
        ServiceLocator::setContainer($this->container);
        
        $this->assertTrue(ServiceLocator::hasParameter('test.param'));
        $this->assertFalse(ServiceLocator::hasParameter('non.existent'));
    }

    public function testHas(): void
    {
        ServiceLocator::setContainer($this->container);
        
        $this->assertTrue(ServiceLocator::has('test.service'));
        $this->assertFalse(ServiceLocator::has('non.existent'));
    }

    public function testGetWithoutContainer(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Container has not been set yet');
        
        ServiceLocator::get('test.service');
    }

    public function testGetParameterWithoutContainer(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Container has not been set yet');
        
        ServiceLocator::getParameter('test.param');
    }
}
