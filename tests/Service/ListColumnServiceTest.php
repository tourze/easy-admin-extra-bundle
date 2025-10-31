<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdminExtraBundle\Service\ListColumnService;

/**
 * @internal
 */
#[CoversClass(ListColumnService::class)]
final class ListColumnServiceTest extends TestCase
{
    private ListColumnService $listColumnService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listColumnService = new ListColumnService();
    }

    public function testAppendSetsFieldDisabled(): void
    {
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: ListColumnService 在处理属性时需要 ReflectionProperty 类型参数
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $property = $this->createMock(\ReflectionProperty::class);
        $field = TextField::new('testField');
        $listColumn = new ListColumn();

        $this->listColumnService->append($property, $field, $listColumn);

        self::assertTrue($field->getAsDto()->getFormTypeOption('disabled'));
    }

    public function testAppendSetsSortableWhenSorterIsTrue(): void
    {
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: ListColumnService 在处理属性时需要 ReflectionProperty 类型参数
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $property = $this->createMock(\ReflectionProperty::class);
        $field = TextField::new('testField');
        $listColumn = new ListColumn();
        $listColumn->sorter = true;

        $this->listColumnService->append($property, $field, $listColumn);

        self::assertTrue($field->getAsDto()->isSortable());
    }

    public function testAppendSetsLabelWhenTitleIsNotEmpty(): void
    {
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: ListColumnService 在处理属性时需要 ReflectionProperty 类型参数
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $property = $this->createMock(\ReflectionProperty::class);
        $field = TextField::new('testField');
        $listColumn = new ListColumn();
        $listColumn->title = 'Custom Title';

        $this->listColumnService->append($property, $field, $listColumn);

        self::assertSame('Custom Title', $field->getAsDto()->getLabel());
    }
}
