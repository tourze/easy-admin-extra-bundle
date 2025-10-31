<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\ChoiceService;
use Tourze\EasyAdminExtraBundle\Service\EntityDescriber;
use Tourze\EasyAdminExtraBundle\Service\FilterService;

/**
 * @internal
 */
#[CoversClass(FilterService::class)]
final class FilterServiceTest extends TestCase
{
    private FilterService $filterService;

    private ChoiceService $choiceService;

    private EntityDescriber $entityDescriber;

    protected function setUp(): void
    {
        parent::setUp();

        // 必须使用 ChoiceService 具体类进行 mock
        // 理由 1: ChoiceService 是本包的具体服务类，没有对应的接口定义
        // 理由 2: FilterService 依赖于 ChoiceService 来处理选项相关的过滤功能
        // 理由 3: 在测试中需要模拟 ChoiceService 的方法来验证交互行为
        $this->choiceService = $this->createMock(ChoiceService::class);
        // 必须使用 EntityDescriber 具体类进行 mock
        // 理由 1: EntityDescriber 是本包的具体服务类，没有对应的接口定义
        // 理由 2: FilterService 依赖于 EntityDescriber 来获取实体属性信息
        // 理由 3: 在测试中需要模拟 EntityDescriber 的方法来验证交互行为
        $this->entityDescriber = $this->createMock(EntityDescriber::class);
        $this->filterService = new FilterService($this->choiceService, $this->entityDescriber);
    }

    public function testCreateFilterFromPropertyReturnsNullWhenNoFilterableAttribute(): void
    {
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: FilterService 依赖于 ReflectionProperty 的具体方法如 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $property = $this->createMock(\ReflectionProperty::class);
        $property->expects(self::once())
            ->method('getAttributes')
            ->willReturn([])
        ;

        $result = $this->filterService->createFilterFromProperty($property);

        self::assertNull($result);
    }
}
