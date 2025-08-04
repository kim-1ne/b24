<?php

namespace Kim1ne\B24\Application;

use Bitrix\UI\EntitySelector\BaseProvider;

final class EntitySelector
{
    const EXTENSION_NAME = 'kim1ne.b24.entity-selector';

    private static array $selectorId2options = [];

    public function __construct(
        public readonly string $entityId,
        public readonly string $providerClass,
        public readonly array $options,
    ) {}

    public function toArray(): array
    {
        if (is_subclass_of($this->providerClass, BaseProvider::class) === false) {
            throw new \Exception('Provider class ' . $this->providerClass . ' does not implement ' . BaseProvider::class);
        }

        self::$selectorId2options[$this->entityId] = $this->options;

        return [
            'entityId' => $this->entityId,
            'provider' => [
                'moduleId' => Application::MODULE_NAME,
                'className' => $this->providerClass,
            ],
        ];
    }

    public static function load(): void
    {
        if (empty(self::$selectorId2options)) {
            return;
        }

        $configExt = [];

        $entities = [];

        foreach (self::$selectorId2options as $entityId => $option) {
            if (empty($option)) {
                continue;
            }

            $entities[] = [
                'id' => $entityId,
                'options' => $option
            ];
        }

        $configExt['settings'] = [
            'entities' => $entities
        ];

        self::register($configExt);
    }

    private static function register(array $configExt): void
    {
        \CJSCore::registerExt(self::EXTENSION_NAME, $configExt);
        self::$selectorId2options = [];
    }
}
