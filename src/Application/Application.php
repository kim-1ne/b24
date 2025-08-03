<?php

namespace B24\Devtools\Application;

use B24\Devtools\Crm\Replacement\Container;
use Bitrix\Main\Config;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Dotenv\Dotenv;

final class Application
{
    const MODULE_NAME = 'b24.devtools';

    private bool $isRun = false;

    private bool $isLoadEnv = false;

    private string $envDir;

    private ?Config\Configuration $configurationModule = null;

    public function __construct(
        private readonly Configuration $configuration = new Configuration()
    ) {}

    public function replaceCrmServiceContainer(array $smartCode2factory): self
    {
        $this->configuration->setLazyService('crm.service.container', function () use ($smartCode2factory) {
            return new Container($smartCode2factory);
        });

        $this->configuration->setLazyService(Container::class, function () {
            return ServiceLocator::getInstance()->get('crm.service.container');
        });

        return $this;
    }

    public function run(): void
    {
        if ($this->isRun || ServiceLocator::getInstance()->has(self::class)) {
            return;
        }

        $this
            ->includeModule()
            ->registerInDI()
            ->loadEnv()
            ->registerConfig()
            ->finalize();
    }

    private function registerInDI(): self
    {
        $this->configuration->setLazyService(self::class, function () {
            return $this;
        });

        return $this;
    }

    public function setDirEnv(string $envDir): self
    {
        $this->envDir = $envDir;
        return $this;
    }

    private function loadEnv(): self
    {
        if (!isset($this->envDir) || $this->isLoadEnv) {
            return $this;
        }

        $dotenv = Dotenv::createImmutable($this->envDir);
        $dotenv->safeLoad();

        $this->isLoadEnv = true;
        return $this;
    }

    public function getEnv(?string $name = null): mixed
    {
        if ($name) {
            return $_ENV[$name] ?? null;
        }

        return $_ENV;
    }

    public static function get(): self
    {
        return ServiceLocator::getInstance()->get(self::class);
    }

    public static function isInclude(): bool
    {
        return Loader::includeModule(self::MODULE_NAME);
    }

    public function getConfiguration(): Config\Configuration
    {
        if ($this->configurationModule === null) {
            $this->configurationModule = Config\Configuration::getInstance(self::MODULE_NAME);
        }

        return $this->configurationModule;
    }

    private function includeModule(): self
    {
        ModuleManager::getInstalledModules();

        $this->addInPropertyClass(Loader::class, 'loadedModules', self::MODULE_NAME, true);

        $this->addInPropertyClass(ModuleManager::class, 'installedModules', self::MODULE_NAME, [
            'ID' => self::MODULE_NAME
        ]);

        return $this;
    }

    private function addInPropertyClass(string $className, string $property, string $key, mixed $data): void
    {
        $reflectionProperty = new \ReflectionProperty($className, $property);

        $propData = $reflectionProperty->getValue();

        $propData[$key] = $data;

        $reflectionProperty->setValue($propData);
    }

    private function registerConfig(): self
    {
        $config = $this->getConfiguration();

        foreach ($this->configuration->get() as $k => $v) {
            $config[$k] = $v;
        }

        ServiceLocator::getInstance()->registerByModuleSettings(self::MODULE_NAME);

        return $this;
    }

    private function finalize(): self
    {
        EntitySelector::load();

        $this->isRun = true;

        return $this;
    }
}
