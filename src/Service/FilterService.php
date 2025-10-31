<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;

readonly class FilterService
{
    public function __construct(
        private ChoiceService $choiceService,
        private EntityDescriber $entityDescriber,
    ) {
    }

    public function createFilterFromProperty(\ReflectionProperty $property): ?FilterInterface
    {
        $filterableAttr = $property->getAttributes(Filterable::class)[0] ?? null;
        if (null === $filterableAttr) {
            return null;
        }

        $filterable = $filterableAttr->newInstance();
        assert($filterable instanceof Filterable);
        $propertyName = $property->getName();

        // 检查是否是关联字段
        $manyToOneAttr = $property->getAttributes(ORM\ManyToOne::class)[0] ?? null;
        $oneToManyAttr = $property->getAttributes(ORM\OneToMany::class)[0] ?? null;
        $oneToOneAttr = $property->getAttributes(ORM\OneToOne::class)[0] ?? null;
        $manyToManyAttr = $property->getAttributes(ORM\ManyToMany::class)[0] ?? null;

        if (null !== $manyToOneAttr || null !== $oneToManyAttr || null !== $oneToOneAttr || null !== $manyToManyAttr) {
            $filter = EntityFilter::new($propertyName)->canSelectMultiple();
            $label = $this->entityDescriber->getPropertyLabel($property);
            $filter->setLabel($label);

            return $filter;
        }

        // 获取属性类型
        $type = $property->getType();
        if (null === $type) {
            return null;
        }

        // 根据属性类型和 Filterable 配置添加对应的过滤器
        $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'mixed';
        $filter = match (true) {
            'bool' === $typeName || 'boolean' === $typeName => BooleanFilter::new($propertyName),
            'int' === $typeName || 'integer' === $typeName => NumericFilter::new($propertyName),
            'float' === $typeName || 'double' === $typeName => NumericFilter::new($propertyName),
            \DateTimeInterface::class === $typeName => DateTimeFilter::new($propertyName),
            \DateTimeImmutable::class === $typeName => DateTimeFilter::new($propertyName),
            \DateTime::class === $typeName => DateTimeFilter::new($propertyName),
            default => $this->determineFieldByDoctrineType($property),
        };

        // 设置标题
        $label = $this->entityDescriber->getPropertyLabel($property);
        if (null !== $filter && method_exists($filter, 'setLabel')) {
            $filter->setLabel($label);
        }

        return $filter;
    }

    private function determineFieldByDoctrineType(\ReflectionProperty $property): ?FilterInterface
    {
        // 获取 Doctrine ORM Column 注解
        $column = $property->getAttributes(ORM\Column::class)[0] ?? null;
        if (null === $column) {
            return null;
        }
        $column = $column->newInstance();
        /** @var ORM\Column $column */
        if (null !== $column->enumType) {
            /** @var class-string<\BackedEnum> $enumType */
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
