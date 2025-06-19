<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Tourze\EasyAdmin\Attribute\Column\FileSizeColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;

class ListColumnService
{
    public function append(\ReflectionProperty $property, FieldInterface $field, ListColumn $listColumn): void
    {
        // 在列表中显示的字段，默认我们都要当作禁用编辑
        // 这样子才能跟 FormField 那边搭配啊
        if (method_exists($field, 'setDisabled')) {
            $field->setDisabled();
        }

        if (method_exists($field, 'setSortable')) {
            $field->setSortable($field instanceof IdField || !!$listColumn->sorter);
        }

        if ($listColumn->title !== null && method_exists($field, 'setLabel')) {
            $field->setLabel($listColumn->title);
        }
        if ($listColumn->sorter && method_exists($field, 'setSortable')) {
            $field->setSortable(true);
        }
        if ($listColumn->width > 0 && method_exists($field, 'addCssClass')) {
            $field->addCssClass('width-' . $listColumn->width);
        }

        if (!empty($property->getAttributes(FileSizeColumn::class)) && method_exists($field, 'formatValue')) {
            $field->formatValue(FileSizeColumn::format(...));
        }
    }
}
