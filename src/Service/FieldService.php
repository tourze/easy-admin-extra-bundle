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
use ReflectionProperty;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Column\PictureColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Field\RichTextField;
use Tourze\EcolBundle\Service\Engine;

class FieldService
{
    public function __construct(
        private readonly Engine $engine,
        private readonly ListColumnService $listColumnService,
        private readonly FormFieldService $formFieldService,
        private readonly ChoiceService $choiceService,
        private readonly EntityDescriber $entityDescriber,
    )
    {
    }

    private function getValidListColumnAttribute(\ReflectionProperty $property): ?ListColumn
    {
        $attr = $property->getAttributes(ListColumn::class)[0] ?? null;
        $listColumn = $attr?->newInstance();
        /** @var ListColumn $listColumn */

        if ($listColumn !== null && $listColumn->showExpression !== null && $this->engine->evaluate($listColumn->showExpression) === false) {
            return null;
        }
        return $listColumn;
    }

    private function getValidFormFieldAttribute(\ReflectionProperty $property): ?FormField
    {
        $attr = $property->getAttributes(FormField::class)[0] ?? null;
        $formField = $attr?->newInstance();
        /** @var FormField $formField */

        if ($formField !== null && $formField->showExpression !== null && $this->engine->evaluate($formField->showExpression) === false) {
            return null;
        }
        return $formField;
    }

    private function createAssociationField(
        \ReflectionProperty $property,
        ?\ReflectionAttribute $manyToOneAttr,
        ?\ReflectionAttribute $oneToManyAttr,
        ?\ReflectionAttribute $oneToOneAttr
    ): FieldInterface {
        $propertyName = $property->getName();
        $field = AssociationField::new($propertyName);

        // 根据关系类型配置字段
        if ($manyToOneAttr !== null) {
            $args = $manyToOneAttr->getArguments();
            $targetEntity = $args['targetEntity'] ?? null;
            if ($targetEntity) {
                $field->setFormTypeOption('class', $targetEntity);
            }
            // ManyToOne 默认显示为下拉选择
            $field->renderAsNativeWidget();
        } elseif ($oneToManyAttr !== null) {
//            $args = $oneToManyAttr->getArguments();
//            $targetEntity = $args['targetEntity'] ?? null;
//            if ($targetEntity) {
//                $field->setFormTypeOption('class', $targetEntity);
//            }
//            // OneToMany 默认显示为多选
//            $field->allowMultipleChoices();
        } elseif ($oneToOneAttr !== null) {
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
        if ($column === null) {
            return null;
        }
        $column = $column->newInstance();
        /** @var ORM\Column $column */

        if ($column->enumType !== null) {
            /** @var class-string<\BackedEnum> $enumType */
            $enumType = $column->enumType;

            $choices = [];
            foreach ($this->choiceService->createChoicesFromEnum($enumType) as $label => $value) {
                $choices[$label] = $pageName === 'index' ? $value->value : $value;
            }
            return ChoiceField::new($property->getName())
                ->autocomplete()
                ->setChoices($choices);
        }

        $type = $column->type;
        return match($type) {
            Types::TEXT => TextareaField::new($property->getName()),
            Types::INTEGER => IntegerField::new($property->getName()),
            Types::DECIMAL, Types::FLOAT => NumberField::new($property->getName()),
            Types::BOOLEAN => BooleanField::new($property->getName()),
            Types::DATE_MUTABLE, Types::DATE_IMMUTABLE => DateField::new($property->getName()),
            Types::DATETIME_MUTABLE, Types::DATETIME_IMMUTABLE => DateTimeField::new($property->getName()),
            Types::JSON => ArrayField::new($property->getName()),
            default => TextField::new($property->getName())
        };
    }

    public function createFieldFromProperty(ReflectionProperty $property, string $pageName): ?FieldInterface
    {
        // 获取属性类型
        $type = $property->getType();
        if ($type === null) {
            return null;
        }

        $listColumn = $this->getValidListColumnAttribute($property);
        $formField = $this->getValidFormFieldAttribute($property);

        // 列表和表单都没，那就不返回
        if ($listColumn === null && $formField === null) {
            return null;
        }

        // 先检查是否是关联关系
        $manyToOneAttr = $property->getAttributes(ORM\ManyToOne::class)[0] ?? null;
        $oneToManyAttr = $property->getAttributes(ORM\OneToMany::class)[0] ?? null;
        $oneToOneAttr = $property->getAttributes(ORM\OneToOne::class)[0] ?? null;

        $field = null;

        if ($manyToOneAttr !== null || $oneToManyAttr !== null || $oneToOneAttr !== null) {
            $field = $this->createAssociationField($property, $manyToOneAttr, $oneToManyAttr, $oneToOneAttr);
        }

        if ($field === null) {
            $field = $this->createPropertyField($property, $pageName);

            if ($field === null) {
                return null;
            }

            // 应用 ListColumn 配置
            if ($listColumn !== null) {
                $this->listColumnService->append($property, $field, $listColumn);
            }

            // 应用 FormField 配置
            if ($formField !== null) {
                $this->formFieldService->append($field, $formField);
            }
        }

        // 兜底的判断
        if (!$field->getAsDto()->getLabel()) {
            if (method_exists($field, 'setLabel')) {
                $field->setLabel($this->entityDescriber->getPropertyLabel($property));
            }
        }

        if ($listColumn === null) {
            if (method_exists($field, 'hideOnIndex')) {
                $field->hideOnIndex();
            }
        }
        if ($formField === null) {
            if (method_exists($field, 'hideOnForm')) {
                $field->hideOnForm();
            }
        }

        return $field;
    }

    private function createPropertyField(ReflectionProperty $property, string $pageName): ?FieldInterface
    {
        $propertyName = $property->getName();
        $type = $property->getType();

        // 根据属性类型创建对应的 Field
        $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'mixed';
        $field = match(true) {
            $propertyName === 'id' => IdField::new($propertyName)->setMaxLength(-1),
            !empty($property->getAttributes(PictureColumn::class)) => ImageField::new($propertyName)
                ->setUploadDir('public/uploads/')
                ->setBasePath('/uploads/')
                ->setUploadedFileNamePattern(function (UploadedFile $file): string {
                    return date('Y/m/d/') . md5(uniqid()) . '.' . $file->guessExtension();
                })
                ->setFormTypeOption('upload_new', function (UploadedFile $file, string $uploadDir, string $fileName): void {
                    if (($extraDirs = dirname($fileName)) !== '.') {
                        $uploadDir .= $extraDirs;
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0750, true);
                        }
                    }
                    $file->move($uploadDir, $fileName);
                }),
            $typeName === 'bool' || $typeName === 'boolean' => BooleanField::new($propertyName),
            $typeName === 'int' || $typeName === 'integer' => IntegerField::new($propertyName),
            $typeName === 'float' || $typeName === 'double' => NumberField::new($propertyName),
            $typeName === 'array' => ArrayField::new($propertyName),
            $typeName === \DateTimeInterface::class, $typeName === \DateTimeImmutable::class => DateTimeField::new($propertyName),
            $typeName === \DateTime::class => DateTimeField::new($propertyName),
            $typeName === \DateInterval::class => DateField::new($propertyName),
            !empty($property->getAttributes(RichTextField::class)) => TextEditorField::new($propertyName),
            default => $this->determineFieldByDoctrineType($property, $pageName)
        };

        if ($field === null) {
            return null;
        }

        if (method_exists($field, 'setLabel')) {
            $field->setLabel($this->entityDescriber->getPropertyLabel($property));
        }
        return $field;
    }
}
