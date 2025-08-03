# Фреймворк для фреймворка Bitrix24

> Библиотека регистрируется как модуль в системе битрикс с помощью [Reflection API](https://www.php.net/manual/ru/book.reflection.php) после вызова метода run у объекта B24\Devtools\Application\Application. 

# Установка
```php
composer require kim-1ne/b24
```
- Удобное API для работы с DI-контейнером Bitrix24
- Подмена родных Entity-selector'ов Bitrix24
- Удобная замена Service-контейнера и подмена фабрики
- .env окружение
- инициализация [контроллеров](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=6436&LESSON_PATH=3913.3516.5062.3750.6436&ysclid=mdvsayimn9358699657) без создания модулей
## Пример использования:
```php
use B24\Devtools\Application\Application;

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
