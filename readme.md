```php

use VigihDev\SymfonyBridge\Config\ConfigBridge;
use VigihDev\SymfonyBridge\Config\Service\ServiceLocator;

require __DIR__ . '/vendor/autoload.php';

$config = ConfigBridge::boot(__DIR__);
$container = $config->container();

var_dump(
    $config->container()->getParameter('app.env'),
    $container->getParameter('app.env'),
    getenv('APP_ENV'),
    $config->container()->get(UserContract::class),
    ServiceLocator::getParameter('app.env')
);
```

```php

use VigihDev\SymfonyBridge\Config\ConfigBridge;
use Tests\Entity\AppConfig;
use Tests\Contracts\UserContract;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use VigihDev\SymfonyBridge\Config\Service\ServiceLocator;

require __DIR__ . '/vendor/autoload.php';

$config = new ConfigBridge(__DIR__);
$config->loadEnv();
$config->loadConfig(__DIR__ . '/config');
$config->addConfiguration(new AppConfig());

var_dump(
    $config->container()->getParameter('app.env'),
    $container->getParameter('app.env'),
    getenv('APP_ENV'),
    $config->container()->get(UserContract::class),
    ServiceLocator::getParameter('app.env')
);

// $appConfig = $config->get(AppConfig::class);

```
