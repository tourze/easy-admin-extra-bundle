<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Column\PictureColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Field\RichTextField;
use Tourze\EcolBundle\Service\Engine;

readonly class FieldService
{
    public function __construct(
        private Engine $engine,
        private ListColumnService $listColumnService,
        private FormFieldService $formFieldService,
        private ChoiceService $choiceService,
        private EntityDescriber $entityDescriber,
    ) {
    }

    private function getValidListColumnAttribute(\ReflectionProperty $property): ?ListColumn
    {
        $attr = $property->getAttributes(ListColumn::class)[0] ?? null;
        $listColumn = $attr?->newInstance();
        /** @var ListColumn $listColumn */
        if (null !== $listColumn && null !== $listColumn->showExpression && false === $this->engine->evaluate($listColumn->showExpression)) {
            return null;
        }

        return $listColumn;
    }

    private function getValidFormFieldAttribute(\ReflectionProperty $property): ?FormField
    {
        $attr = $property->getAttributes(FormField::class)[0] ?? null;
        $formField = $attr?->newInstance();
        /** @var FormField $formField */
        if (null !== $formField && null !== $formField->showExpression && false === $this->engine->evaluate($formField->showExpression)) {
            return null;
        }

        return $formField;
    }

    /**
     * @param ?\ReflectionAttribute<object> $manyToOneAttr
     * @param ?\ReflectionAttribute<object> $oneToManyAttr
     * @param ?\ReflectionAttribute<object> $oneToOneAttr
     */
    private function createAssociationField(
        \ReflectionProperty $property,
        ?\ReflectionAttribute $manyToOneAttr,
        ?\ReflectionAttribute $oneToManyAttr,
        ?\ReflectionAttribute $oneToOneAttr,
    ): FieldInterface {
        $propertyName = $property->getName();
        $field = AssociationField::new($propertyName);

        // 根据关系类型配置字段
        if (null !== $manyToOneAttr) {
            $args = $manyToOneAttr->getArguments();
            $targetEntity = $args['targetEntity'] ?? null;
            if ($targetEntity) {
                $field->setFormTypeOption('class', $targetEntity);
            }
            // ManyToOne 默认显示为下拉选择
            $field->renderAsNativeWidget();
        } elseif (null !== $oneToManyAttr) {
            //            $args = $oneToManyAttr->getArguments();
            //            $targetEntity = $args['targetEntity'] ?? null;
            //            if ($targetEntity) {
            //                $field->setFormTypeOption('class', $targetEntity);
            //            }
            //            // OneToMany 默认显示为多选
            //            $field->allowMultipleChoices();
        } elseif (null !== $oneToOneAttr) {
            $args = $oneToOneAttr->getArguments();
            $targetEntity = $args['targetEntity'] ?? null;
            if ($targetEntity) {
                $field->setFormTypeOption('class', $targetEntity);
            }
            // OneToOne 默认显示为单选
            $field->renderAsNativeWidget();
        }

        return $field;
    }

    /**
     * @see https://symfony.com/bundles/EasyAdminBundle/4.x/fields/ChoiceField.html#setchoices
     */
    private function determineFieldByDoctrineType(\ReflectionProperty $property, string $pageName): ?FieldInterface
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

            $choices = [];
            foreach ($this->choiceService->createChoicesFromEnum($enumType) as $label => $value) {
                $choices[$label] = 'index' === $pageName ? $value->value : $value;
            }

            return ChoiceField::new($property->getName())
                ->autocomplete()
                ->setChoices($choices)
            ;
        }

        $type = $column->type;

        return match ($type) {
            Types::TEXT => TextareaField::new($property->getName()),
            Types::INTEGER => IntegerField::new($property->getName()),
            Types::DECIMAL, Types::FLOAT => NumberField::new($property->getName()),
            Types::BOOLEAN => BooleanField::new($property->getName()),
            Types::DATE_MUTABLE, Types::DATE_IMMUTABLE => DateField::new($property->getName()),
            Types::DATETIME_MUTABLE, Types::DATETIME_IMMUTABLE => DateTimeField::new($property->getName()),
            Types::JSON => ArrayField::new($property->getName()),
            default => TextField::new($property->getName()),
        };
    }

    public function createFieldFromProperty(\ReflectionProperty $property, string $pageName): ?FieldInterface
    {
        if (null === $property->getType()) {
            return null;
        }

        $fieldAttributes = $this->getFieldAttributes($property);
        if (!$this->shouldCreateField($fieldAttributes)) {
            return null;
        }

        $field = $this->createFieldInstance($property, $pageName);
        if (null === $field) {
            return null;
        }

        $this->configureField($field, $property, $fieldAttributes);
        $this->configureFieldVisibility($field, $fieldAttributes);

        return $field;
    }

    /**
     * @return array{listColumn: ?ListColumn, formField: ?FormField, associations: array<string, ?\ReflectionAttribute<object>>}
     */
    private function getFieldAttributes(\ReflectionProperty $property): array
    {
        return [
            'listColumn' => $this->getValidListColumnAttribute($property),
            'formField' => $this->getValidFormFieldAttribute($property),
            'associations' => $this->getAssociationAttributes($property),
        ];
    }

    /**
     * @param array{listColumn: ?ListColumn, formField: ?FormField, associations: array<string, ?\ReflectionAttribute<object>>} $fieldAttributes
     */
    private function shouldCreateField(array $fieldAttributes): bool
    {
        return null !== $fieldAttributes['listColumn'] || null !== $fieldAttributes['formField'];
    }

    /**
     * @return array<string, ?\ReflectionAttribute<object>>
     */
    private function getAssociationAttributes(\ReflectionProperty $property): array
    {
        return [
            'manyToOne' => $property->getAttributes(ORM\ManyToOne::class)[0] ?? null,
            'oneToMany' => $property->getAttributes(ORM\OneToMany::class)[0] ?? null,
            'oneToOne' => $property->getAttributes(ORM\OneToOne::class)[0] ?? null,
        ];
    }

    private function createFieldInstance(\ReflectionProperty $property, string $pageName): ?FieldInterface
    {
        $associations = $this->getAssociationAttributes($property);

        if ($this->hasAssociations($associations)) {
            return $this->createAssociationField(
                $property,
                $associations['manyToOne'],
                $associations['oneToMany'],
                $associations['oneToOne']
            );
        }

        return $this->createPropertyField($property, $pageName);
    }

    /**
     * @param array<string, ?\ReflectionAttribute<object>> $associations
     */
    private function hasAssociations(array $associations): bool
    {
        return null !== $associations['manyToOne']
            || null !== $associations['oneToMany']
            || null !== $associations['oneToOne'];
    }

    /**
     * @param array{listColumn: ?ListColumn, formField: ?FormField, associations: array<string, ?\ReflectionAttribute<object>>} $fieldAttributes
     */
    private function configureField(FieldInterface $field, \ReflectionProperty $property, array $fieldAttributes): void
    {
        if (!$this->hasAssociations($fieldAttributes['associations'])) {
            $this->applyAttributeConfigurations($field, $property, $fieldAttributes);
        }

        $this->ensureFieldLabel($field, $property);
    }

    /**
     * @param array{listColumn: ?ListColumn, formField: ?FormField, associations: array<string, ?\ReflectionAttribute<object>>} $fieldAttributes
     */
    private function applyAttributeConfigurations(FieldInterface $field, \ReflectionProperty $property, array $fieldAttributes): void
    {
        if (null !== $fieldAttributes['listColumn']) {
            $this->listColumnService->append($property, $field, $fieldAttributes['listColumn']);
        }

        if (null !== $fieldAttributes['formField']) {
            $this->formFieldService->append($field, $fieldAttributes['formField']);
        }
    }

    private function ensureFieldLabel(FieldInterface $field, \ReflectionProperty $property): void
    {
        $label = $field->getAsDto()->getLabel();
        if (!(is_string($label) && '' !== $label) && method_exists($field, 'setLabel')) {
            $field->setLabel($this->entityDescriber->getPropertyLabel($property));
        }
    }

    /**
     * @param array{listColumn: ?ListColumn, formField: ?FormField, associations: array<string, ?\ReflectionAttribute<object>>} $fieldAttributes
     */
    private function configureFieldVisibility(FieldInterface $field, array $fieldAttributes): void
    {
        if (null === $fieldAttributes['listColumn'] && method_exists($field, 'hideOnIndex')) {
            $field->hideOnIndex();
        }

        if (null === $fieldAttributes['formField'] && method_exists($field, 'hideOnForm')) {
            $field->hideOnForm();
        }
    }

    private function createPropertyField(\ReflectionProperty $property, string $pageName): ?FieldInterface
    {
        $propertyName = $property->getName();
        $type = $property->getType();

        // 根据属性类型创建对应的 Field
        $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'mixed';
        $field = match (true) {
            'id' === $propertyName => IdField::new($propertyName)->setMaxLength(-1),
            [] !== $property->getAttributes(PictureColumn::class) => ImageField::new($propertyName)
                ->setUploadDir('public/uploads/')
                ->setBasePath('/uploads/')
                ->setUploadedFileNamePattern(function (UploadedFile $file): string {
                    return date('Y/m/d/') . md5(uniqid()) . '.' . $file->guessExtension();
                })
                ->setFormTypeOption('upload_new', function (UploadedFile $file, string $uploadDir, string $fileName): void {
                    if (($extraDirs = dirname($fileName)) !== '.') {
                        $uploadDir .= $extraDirs;
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0o750, true);
                        }
                    }
                    $file->move($uploadDir, $fileName);
                }),
            'bool' === $typeName || 'boolean' === $typeName => BooleanField::new($propertyName),
            'int' === $typeName || 'integer' === $typeName => IntegerField::new($propertyName),
            'float' === $typeName || 'double' === $typeName => NumberField::new($propertyName),
            'array' === $typeName => ArrayField::new($propertyName),
            \DateTimeInterface::class === $typeName, \DateTimeImmutable::class === $typeName => DateTimeField::new($propertyName),
            \DateTime::class === $typeName => DateTimeField::new($propertyName),
            \DateInterval::class === $typeName => DateField::new($propertyName),
            [] !== $property->getAttributes(RichTextField::class) => TextEditorField::new($propertyName),
            default => $this->determineFieldByDoctrineType($property, $pageName),
        };

        if (null === $field) {
            return null;
        }

        if (method_exists($field, 'setLabel')) {
            $field->setLabel($this->entityDescriber->getPropertyLabel($property));
        }

        return $field;
    }
}
