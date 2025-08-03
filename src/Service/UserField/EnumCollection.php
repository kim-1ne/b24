<?php

namespace B24\Devtools\Service\UserField;

class EnumCollection implements \IteratorAggregate
{
    public function __construct(
        /**
         * @var Enum[] $enums
         */
        private array $enums
    ) {}

    /**
     * @return Enum[]
     */
    public function get(): array
    {
        return $this->enums;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->enums);
    }

    public function findByXmlId(string $xmlId): ?Enum
    {
        return $this->find('xmlId', $xmlId);
    }

    private function find(string $propertyName, string|bool $value): ?Enum
    {
        foreach ($this->enums as $enum) {
            if ($enum->$propertyName === $value) {
                return $enum;
            }
        }

        return null;
    }

    public function findByValue(string $value): ?Enum
    {
        return $this->find('value', $value);
    }

    public function findDefault(): ?Enum
    {
        return $this->find('isDefault', true);
    }
}
