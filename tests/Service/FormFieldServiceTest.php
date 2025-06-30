<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\FormFieldService;
use Tourze\EasyAdmin\Attribute\Field\FormField;

class FormFieldServiceTest extends TestCase
{
    private FormFieldService $formFieldService;

    protected function setUp(): void
    {
        $this->formFieldService = new FormFieldService();
    }

    public function testAppendSetsLabelWhenTitleIsNotEmpty(): void
    {
        $field = TextField::new('testField');
        $formField = new FormField();
        $formField->title = 'Test Label';

        $this->formFieldService->append($field, $formField);

        self::assertSame('Test Label', $field->getAsDto()->getLabel());
    }

    public function testAppendSetsDisabledWhenCanEditIsFalse(): void
    {
        $field = TextField::new('testField');
        $formField = new FormField();
        $formField->canEdit = false;

        $this->formFieldService->append($field, $formField);

        self::assertTrue($field->getAsDto()->getFormTypeOption('disabled'));
    }

    public function testAppendSetsRequiredWhenFormFieldIsRequired(): void
    {
        $field = TextField::new('testField');
        $formField = new FormField();
        $formField->required = true;

        $this->formFieldService->append($field, $formField);

        self::assertTrue($field->getAsDto()->getFormTypeOption('required'));
    }
}