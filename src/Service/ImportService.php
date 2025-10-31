<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;

class ImportService
{
    /**
     * @param \ReflectionClass<object> $reflectionClass
     * @return iterable<\ReflectionProperty>
     */
    public function getPossibleProperties(\ReflectionClass $reflectionClass): iterable
    {
        foreach ($reflectionClass->getProperties() as $property) {
            // 主键
            if ([] !== $property->getAttributes(Id::class)) {
                yield $property;
                continue;
            }

            $column = $property->getAttributes(Column::class);
            if ([] === $column) {
                continue;
            }
            $column = $column[0]->newInstance();
            /** @var Column $column */
            // 唯一主键也要考虑
            if ($column->unique) {
                yield $property;
            }
        }
    }
}
