<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Tourze\EasyAdmin\Attribute\Field\FormField;

class FormFieldService
{
    public function append(FieldInterface $field, FormField $formField): void
    {
        if ($formField->title) {
            $field->setLabel($formField->title);
        }
        $field->setDisabled(!$formField->canEdit);
        if ($formField->required) {
            $field->setRequired(true);
        }
    }
}
