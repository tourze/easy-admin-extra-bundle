<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\ListColumnService;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;

class ListColumnServiceTest extends TestCase
{
    private ListColumnService $listColumnService;

    protected function setUp(): void
    {
        $this->listColumnService = new ListColumnService();
    }

    public function testAppendSetsFieldDisabled(): void
    {
        $property = $this->createMock(\ReflectionProperty::class);
        $field = TextField::new('testField');
        $listColumn = new ListColumn();

        $this->listColumnService->append($property, $field, $listColumn);

        self::assertTrue($field->getAsDto()->getFormTypeOption('disabled'));
    }

    public function testAppendSetsSortableWhenSorterIsTrue(): void
    {
        $property = $this->createMock(\ReflectionProperty::class);
        $field = TextField::new('testField');
        $listColumn = new ListColumn();
        $listColumn->sorter = true;

        $this->listColumnService->append($property, $field, $listColumn);

        self::assertTrue($field->getAsDto()->isSortable());
    }

    public function testAppendSetsLabelWhenTitleIsNotEmpty(): void
    {
        $property = $this->createMock(\ReflectionProperty::class);
        $field = TextField::new('testField');
        $listColumn = new ListColumn();
        $listColumn->title = 'Custom Title';

        $this->listColumnService->append($property, $field, $listColumn);

        self::assertSame('Custom Title', $field->getAsDto()->getLabel());
    }
}