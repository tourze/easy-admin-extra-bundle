<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdminExtraBundle\Service\FormFieldService;

/**
 * @internal
 */
#[CoversClass(FormFieldService::class)]
final class FormFieldServiceTest extends TestCase
{
    private FormFieldService $formFieldService;

    protected function setUp(): void
    {
        parent::setUp();

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
