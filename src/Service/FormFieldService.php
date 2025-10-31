<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Tourze\EasyAdmin\Attribute\Field\FormField;

class FormFieldService
{
    public function append(FieldInterface $field, FormField $formField): void
    {
        if ('' !== $formField->title) {
            if (method_exists($field, 'setLabel')) {
                $field->setLabel($formField->title);
            }
        }
        if (method_exists($field, 'setDisabled')) {
            $field->setDisabled(!$formField->canEdit);
        }
        if ($formField->required && method_exists($field, 'setRequired')) {
            $field->setRequired(true);
        }
    }
}
