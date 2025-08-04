<?php

namespace Kim1ne\B24\Crm\Replacement;

use Bitrix\Crm\Service;

\CModule::IncludeModule('crm');

class Container extends Service\Container
{
    public function __construct(private readonly array $smartCode2factory = []) {}

    public function getFactory(int $entityTypeId): ?Service\Factory
    {
        return $this->getOwnFactory($entityTypeId);
    }

    private function getOwnFactory(int $entityTypeId): ?Service\Factory
    {
        $type = $this->getTypeByEntityTypeId($entityTypeId);

        if (
            $type === null ||
            ($factoryClass = $this->smartCode2factory[$type->get('CODE')] ?? null) === null
        ) {
            return parent::getFactory($entityTypeId);
        }

        return new $factoryClass($type);
    }
}
