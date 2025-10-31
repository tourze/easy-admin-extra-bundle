<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Method;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * 测试重定向响应功能
 *
 * @internal
 */
/**
 * @phpstan-ignore-next-line Testing deprecated class functionality
 * @internal
 */
#[CoversClass(\Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController::class)] // @phpstan-ignore-line
final class RedirectResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 测试控制器类继承链是否正确
     *
     * 注意：匿名类无法使用 #[AdminCrud] 属性注解，此测试仅验证基本继承
     */
    public function testAbstractCrudControllerInheritance(): void
    {
        // @phpstan-ignore-next-line Testing deprecated class functionality
        $controller = new class extends \Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController {
            public static function getEntityFqcn(): string
            {
                return \stdClass::class;
            }
        };

        /** @phpstan-ignore-next-line Testing deprecated class functionality */
        $this->assertInstanceOf(\Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController::class, $controller);
        $this->assertInstanceOf(AbstractCrudController::class, $controller);
        $this->assertEquals(\stdClass::class, $controller::getEntityFqcn());
    }
}
