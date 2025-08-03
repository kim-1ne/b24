Фреймворк для фреймворка Bitrix24

# Установка
```php
composer require kim-1ne/b24
```
## Пример использования:
```php
yse B24\Devtools\Application\Application;

$configuration = new \B24\Devtools\Application\Configuration();
$configuration
    ->setLazyService('my-service', function () {
        return new MyService();
    })
    ->setService(name: 'my-service', className: MyService::class, construct: $construct)
    ->setController('\\Kim1ne\\B24\\Controller', 'api')
    ->setEntitySelector(
        entityId: 'CRM_DEAL',
        providerClass: new ProviderClass(),
        options: $options  
    );

$application = new Application($configuration);
$application
    ->setDirEnv(dirname(__DIR__, 2))
    ->replaceCrmServiceContainer([
        'SMART_CODE_1' => Factory1::class,
        'SMART_CODE_2' => Factory2::class,
    ])
    ->run();

$application = \Bitrix\Main\DI\ServiceLocator::getInstance()->get(Application::class);
```
