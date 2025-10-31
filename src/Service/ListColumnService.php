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
        $this->disableFieldEditing($field);
        $this->configureSorting($field, $listColumn);
        $this->applyFieldLabel($field, $listColumn);
        $this->applyFieldWidth($field, $listColumn);
        $this->applyFileSizeFormatting($property, $field);
    }

    private function disableFieldEditing(FieldInterface $field): void
    {
        // 在列表中显示的字段，默认我们都要当作禁用编辑
        // 这样子才能跟 FormField 那边搭配啊
        if (method_exists($field, 'setDisabled')) {
            $field->setDisabled();
        }
    }

    private function configureSorting(FieldInterface $field, ListColumn $listColumn): void
    {
        if (!method_exists($field, 'setSortable')) {
            return;
        }

        $shouldSort = $field instanceof IdField || $listColumn->sorter;
        $field->setSortable($shouldSort);
    }

    private function applyFieldLabel(FieldInterface $field, ListColumn $listColumn): void
    {
        if (null !== $listColumn->title && method_exists($field, 'setLabel')) {
            $field->setLabel($listColumn->title);
        }
    }

    private function applyFieldWidth(FieldInterface $field, ListColumn $listColumn): void
    {
        if ($listColumn->width > 0 && method_exists($field, 'addCssClass')) {
            $field->addCssClass('width-' . $listColumn->width);
        }
    }

    private function applyFileSizeFormatting(\ReflectionProperty $property, FieldInterface $field): void
    {
        $hasFileSizeAttribute = [] !== $property->getAttributes(FileSizeColumn::class);

        if ($hasFileSizeAttribute && method_exists($field, 'formatValue')) {
            $field->formatValue(FileSizeColumn::format(...));
        }
    }
}
