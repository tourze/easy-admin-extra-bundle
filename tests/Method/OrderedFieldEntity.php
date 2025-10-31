<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Method;

use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;

/**
 * 带有字段排序注解的测试实体类
 *
 * @internal
 */
class OrderedFieldEntity
{
    public function __construct(
        #[ListColumn(order: 2)]
        #[FormField(order: 2)]
        private readonly string $name = '',

        #[ListColumn(order: 1)]
        #[FormField(order: 1)]
        private readonly string $title = '',

        #[ListColumn(order: 3)]
        #[FormField(order: 3)]
        private readonly string $description = '',
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
