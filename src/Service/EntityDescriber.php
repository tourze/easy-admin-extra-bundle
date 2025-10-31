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
readonly class EntityDescriber
{
    public function __construct(
        private Inflector $inflector,
    ) {
    }

    /**
     * 获取Label值
     */
    public function getPropertyLabel(\ReflectionProperty $property): string
    {
        $name = $this->getOrmCommentLabel($property);

        if ($name === $property->getName()) {
            $name = $this->getAttributeLabel($property) ?? $name;
        }

        return $name;
    }

    private function getOrmCommentLabel(\ReflectionProperty $property): string
    {
        $ormColumn = $property->getAttributes(ORM\Column::class)[0] ?? null;
        if (null === $ormColumn) {
            return $property->getName();
        }

        $attribute = $ormColumn->newInstance();

        return $attribute->options['comment'] ?? $property->getName();
    }

    private function getAttributeLabel(\ReflectionProperty $property): ?string
    {
        return $this->getListColumnTitle($property) ?? $this->getFormFieldTitle($property);
    }

    private function getListColumnTitle(\ReflectionProperty $property): ?string
    {
        $listColumn = $property->getAttributes(ListColumn::class)[0] ?? null;
        if (null === $listColumn) {
            return null;
        }

        $instance = $listColumn->newInstance();

        return $instance->title;
    }

    private function getFormFieldTitle(\ReflectionProperty $property): ?string
    {
        $formField = $property->getAttributes(FormField::class)[0] ?? null;
        if (null === $formField) {
            return null;
        }

        $instance = $formField->newInstance();

        return $instance->title;
    }

    /**
     * 获取指定 Property 在数据库中的字段名
     */
    public function getColumnName(\ReflectionProperty $property, bool $withIdSuffix): string
    {
        // 从 ORM\Column 只读取 name
        $ormColumn = $property->getAttributes(ORM\Column::class);
        if ([] !== $ormColumn) {
            $ormColumn = $ormColumn[0]->newInstance();
            /** @var ORM\Column $ormColumn */
            if (null !== $ormColumn->name) {
                return $this->inflector->toSnakeCase($ormColumn->name);
            }
        }

        if ($withIdSuffix) {
            // 如果是关系字段，则加上id后缀
            if ([] !== $property->getAttributes(ORM\ManyToOne::class) || [] !== $property->getAttributes(ORM\OneToOne::class)) {
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
        if ([] !== $attributes) {
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
