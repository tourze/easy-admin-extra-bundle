<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use Doctrine\ORM\Mapping as ORM;

class ColumnService
{
    /**
     * 格式化枚举的字段
     */
    public function formatEnumColumn(\ReflectionProperty $property, string $title): array
    {
        $isMatch = false;
        $enumClass = null;

        $Column = $property->getAttributes(ORM\Column::class);
        if (!empty($Column)) {
            /** @var ORM\Column $Column */
            $Column = $Column[0]->newInstance();
            if ($Column->enumType !== null) {
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
