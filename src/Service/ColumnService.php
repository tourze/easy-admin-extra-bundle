<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use Doctrine\ORM\Mapping as ORM;

class ColumnService
{
    /**
     * 格式化枚举的字段
     * @return array{title: string, className: string, isMatch: bool}
     */
    public function formatEnumColumn(\ReflectionProperty $property, string $title): array
    {
        $isMatch = false;
        $enumClass = null;

        $Column = $property->getAttributes(ORM\Column::class);
        if ([] !== $Column) {
            $Column = $Column[0]->newInstance();
            assert($Column instanceof ORM\Column);
            if (null !== $Column->enumType) {
                $enumClass = $Column->enumType;
                $isMatch = true;
            }
        }

        return [
            'title' => $title,
            'className' => $enumClass ?? '',
            'isMatch' => $isMatch,
        ];
    }
}
