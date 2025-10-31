<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\ChoiceService;
use Tourze\EasyAdminExtraBundle\Service\EntityDescriber;
use Tourze\EasyAdminExtraBundle\Service\FieldService;
use Tourze\EasyAdminExtraBundle\Service\FormFieldService;
use Tourze\EasyAdminExtraBundle\Service\ListColumnService;
use Tourze\EcolBundle\Service\Engine;

/**
 * @internal
 */
#[CoversClass(FieldService::class)]
final class FieldServiceTest extends TestCase
{
    private FieldService $fieldService;

    private Engine $engine;

    private ListColumnService $listColumnService;

    private FormFieldService $formFieldService;

    private ChoiceService $choiceService;

    private EntityDescriber $entityDescriber;

    protected function setUp(): void
    {
        parent::setUp();

        // 必须使用 Engine 具体类进行 mock
        // 理由 1: Engine 是 EcolBundle 的具体服务类，没有对应的接口定义
        // 理由 2: FieldService 依赖于 Engine 服务来获取上下文信息
        // 理由 3: 在测试中需要模拟 Engine 的 getContext() 方法来返回测试数据
        $this->engine = $this->createMock(Engine::class);
        // 必须使用 ListColumnService 具体类进行 mock
        // 理由 1: ListColumnService 是本包的具体服务类，没有对应的接口定义
        // 理由 2: FieldService 依赖于 ListColumnService 来处理列表列相关的功能
        // 理由 3: 在测试中需要模拟 ListColumnService 的方法来验证交互行为
        $this->listColumnService = $this->createMock(ListColumnService::class);
        // 必须使用 FormFieldService 具体类进行 mock
        // 理由 1: FormFieldService 是本包的具体服务类，没有对应的接口定义
        // 理由 2: FieldService 依赖于 FormFieldService 来处理表单字段相关的功能
        // 理由 3: 在测试中需要模拟 FormFieldService 的方法来验证交互行为
        $this->formFieldService = $this->createMock(FormFieldService::class);
        // 必须使用 ChoiceService 具体类进行 mock
        // 理由 1: ChoiceService 是本包的具体服务类，没有对应的接口定义
        // 理由 2: FieldService 依赖于 ChoiceService 来处理选项相关的功能
        // 理由 3: 在测试中需要模拟 ChoiceService 的方法来验证交互行为
        $this->choiceService = $this->createMock(ChoiceService::class);
        // 必须使用 EntityDescriber 具体类进行 mock
        // 理由 1: EntityDescriber 是本包的具体服务类，没有对应的接口定义
        // 理由 2: FieldService 依赖于 EntityDescriber 来获取实体属性信息
        // 理由 3: 在测试中需要模拟 EntityDescriber 的方法来验证交互行为
        $this->entityDescriber = $this->createMock(EntityDescriber::class);

        $this->fieldService = new FieldService(
            $this->engine,
            $this->listColumnService,
            $this->formFieldService,
            $this->choiceService,
            $this->entityDescriber
        );
    }

    public function testCreateFieldFromPropertyReturnsNullWhenNoType(): void
    {
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: FieldService 依赖于 ReflectionProperty 的具体方法如 getType() 和 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $property = $this->createMock(\ReflectionProperty::class);
        $property->expects(self::once())
            ->method('getType')
            ->willReturn(null)
        ;

        $result = $this->fieldService->createFieldFromProperty($property, 'index');

        self::assertNull($result);
    }

    public function testCreateFieldFromPropertyReturnsNullWithoutListOrFormAttributes(): void
    {
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: FieldService 依赖于 ReflectionProperty 的具体方法如 getType() 和 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $property = $this->createMock(\ReflectionProperty::class);
        // 必须使用 ReflectionNamedType 具体类进行 mock
        // 理由 1: ReflectionNamedType 是 PHP 内置的反射类型类，没有对应的接口可以使用
        // 理由 2: 作为 ReflectionProperty::getType() 的返回值，需要模拟具体类型
        // 理由 3: 在测试中需要模拟类型信息以验证 FieldService 的逻辑
        $type = $this->createMock(\ReflectionNamedType::class);

        $property->expects(self::once())
            ->method('getType')
            ->willReturn($type)
        ;

        // 没有 ListColumn 或 FormField 属性时返回空数组
        $property->expects(self::any())
            ->method('getAttributes')
            ->willReturn([])
        ;

        $result = $this->fieldService->createFieldFromProperty($property, 'index');

        // 没有 ListColumn 或 FormField 时应该返回 null
        self::assertNull($result);
    }
}
