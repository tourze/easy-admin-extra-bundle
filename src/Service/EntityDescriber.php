<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Yiisoft\Strings\Inflector;

/**
 * 读取实体信息
 */
class EntityDescriber
{
    public function __construct(
        private readonly Inflector $inflector,
    )
    {
    }

    /**
     * 获取Label值
     */
    public function getPropertyLabel(\ReflectionProperty $property): string
    {
        $name = $property->getName();

        // 如果是ORM字段，尝试读取注释
        $attributes = $property->getAttributes(ORM\Column::class);
        if (!empty($attributes)) {
            $attribute = $attributes[0]->newInstance();
            /** @var ORM\Column $attribute */
            $name = $attribute->options['comment'] ?? $property->getName();
        }

        // 如果跟属性名一样，说明没变化
        if ($name === $property->getName()) {
            $listColumn = $property->getAttributes(ListColumn::class);
            if (!empty($listColumn)) {
                $listColumn = $listColumn[0]->newInstance();
                /** @var ListColumn $listColumn */
                if ($listColumn->title !== null) {
                    return $listColumn->title;
                }
            }

            $formFiled = $property->getAttributes(FormField::class);
            if (!empty($formFiled)) {
                $formFiled = $formFiled[0]->newInstance();
                /** @var FormField $formFiled */
                if ($formFiled->title !== null) {
                    return $formFiled->title;
                }
            }
        }

        return $name;
    }

    /**
     * 获取指定 Property 在数据库中的字段名
     */
    public function getColumnName(\ReflectionProperty $property, bool $withIdSuffix): string
    {
        // 从 ORM\Column 只读取 name
        $ormColumn = $property->getAttributes(ORM\Column::class);
        if (!empty($ormColumn)) {
            $ormColumn = $ormColumn[0]->newInstance();
            /** @var ORM\Column $ormColumn */
            if ($ormColumn->name !== null) {
                return $this->inflector->toSnakeCase($ormColumn->name);
            }
        }

        if ($withIdSuffix) {
            // 如果是关系字段，则加上id后缀
            if (!empty($property->getAttributes(ORM\ManyToOne::class)) || !empty($property->getAttributes(ORM\OneToOne::class))) {
                return $this->inflector->toSnakeCase($property->getName()) . '_id';
            }
        }

        return $this->inflector->toSnakeCase($property->getName());
    }

    public function getPropertyMinLength(\ReflectionProperty $property): ?int
    {
        return 0;
    }

    public function getPropertyMaxLength(\ReflectionProperty $property): ?int
    {
        $length = null;

        // 如果是ORM字段，尝试读取注释
        $attributes = $property->getAttributes(ORM\Column::class);
        if (!empty($attributes)) {
            $attribute = $attributes[0]->newInstance();
            /** @var ORM\Column $attribute */
            $length = $attribute->length;

            // 不同类型，有不同的默认数据长度
            if (null === $length) {
                $map = [
                    Types::STRING => 1000,
                    Types::INTEGER => 11,
                    Types::BOOLEAN => 2,
                ];
                if (isset($map[$attribute->type])) {
                    $length = $map[$attribute->type];
                }
            }
        }

        return $length;
    }
}
