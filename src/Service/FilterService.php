<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use BackedEnum;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use ReflectionProperty;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;

class FilterService
{
    public function __construct(
        private readonly ChoiceService $choiceService,
        private readonly EntityDescriber $entityDescriber,
    )
    {
    }

    public function createFilterFomProperty(ReflectionProperty $property): ?FilterInterface
    {
        $filterableAttr = $property->getAttributes(Filterable::class)[0] ?? null;
        if ($filterableAttr === null) {
            return null;
        }

        /** @var Filterable $filterable */
        $filterable = $filterableAttr->newInstance();
        $propertyName = $property->getName();

        // 检查是否是关联字段
        $manyToOneAttr = $property->getAttributes(ORM\ManyToOne::class)[0] ?? null;
        $oneToManyAttr = $property->getAttributes(ORM\OneToMany::class)[0] ?? null;
        $oneToOneAttr = $property->getAttributes(ORM\OneToOne::class)[0] ?? null;
        $manyToManyAttr = $property->getAttributes(ORM\ManyToMany::class)[0] ?? null;

        if ($manyToOneAttr !== null || $oneToManyAttr !== null || $oneToOneAttr !== null || $manyToManyAttr !== null) {
            $filter = EntityFilter::new($propertyName)->canSelectMultiple();
            $label = $this->entityDescriber->getPropertyLabel($property);
            $filter->setLabel($label);
            return $filter;
        }

        // 获取属性类型
        $type = $property->getType();
        if ($type === null) {
            return null;
        }

        // 根据属性类型和 Filterable 配置添加对应的过滤器
        $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'mixed';
        $filter = match(true) {
            $typeName === 'bool' || $typeName === 'boolean' => BooleanFilter::new($propertyName),
            $typeName === 'int' || $typeName === 'integer' => NumericFilter::new($propertyName),
            $typeName === 'float' || $typeName === 'double' => NumericFilter::new($propertyName),
            $typeName === \DateTimeInterface::class => DateTimeFilter::new($propertyName),
            $typeName === \DateTimeImmutable::class => DateTimeFilter::new($propertyName),
            $typeName === \DateTime::class => DateTimeFilter::new($propertyName),
            default => $this->determineFieldByDoctrineType($property)
        };

        // 设置标题
        $label = $this->entityDescriber->getPropertyLabel($property);
        if (method_exists($filter, 'setLabel')) {
            $filter->setLabel($label);
        }

        return $filter;
    }

    private function determineFieldByDoctrineType(ReflectionProperty $property): ?FilterInterface
    {
        // 获取 Doctrine ORM Column 注解
        $column = $property->getAttributes(ORM\Column::class)[0] ?? null;
        if ($column === null) {
            return null;
        }
        $column = $column->newInstance();
        /** @var ORM\Column $column */

        if ($column->enumType !== null) {
            /** @var class-string<BackedEnum> $enumType */
            $enumType = $column->enumType;

            $values = [];
            foreach ($this->choiceService->createChoicesFromEnum($enumType) as $label => $value) {
                $values[$value->value] = $label;
            }
            return ChoiceFilter::new($property->getName())->setTranslatableChoices($values);
        }

        return TextFilter::new($property->getName());
    }
}
