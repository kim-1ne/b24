<?php

namespace B24\Devtools\Application;

use Bitrix\Main\DI\ServiceLocator;

final class Configuration
{
    const DEFAULT_CONFIG = 'config.php';

    private array $data = [];

    public function setController(string $namespace, string $name): self
    {
        $this->data['controllers']['namespaces'][$namespace] = $name;
        return $this;
    }

    public function setEntitySelector(string $entityId, string $providerClass, array $options): self
    {
        $entity = (new EntitySelector($entityId, $providerClass, $options))->toArray();

        $this->data['ui.entity-selector']['entities'][] = $entity;

        return $this;
    }

    public function get(): array
    {
        $this->initDefaultConfig();
        return $this->data;
    }

    private function initDefaultConfig(): void
    {
        require_once __DIR__ . '/../' . self::DEFAULT_CONFIG;
    }

    public function setService(string $name, string $className, array|callable $construct = []): self
    {
        $data = [
            'className' => $className,
        ];

        if (!empty($construct)) {
            $data['constructorParams'] = $construct;
        }

        $this->data['services'][$name] = $data;

        return $this;
    }

    public function setLazyService(string $name, callable $callback): self
    {
        $this->data['services'][$name] = [
            'constructor' => function () use ($callback) {
                return $callback(ServiceLocator::getInstance());
            },
        ];

        return $this;
    }
}
