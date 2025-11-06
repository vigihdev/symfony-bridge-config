<?php

declare(strict_types=1);

namespace VigihDev\SymfonyBridge\Config\AttributeInjection;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Inject
{
    public function __construct(
        public string $serviceName
    ) {}
}
