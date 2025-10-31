<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Method;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * 测试搜索字段提取方法
 *
 * @internal
 */
/**
 * @phpstan-ignore-next-line Testing deprecated class functionality
 * @internal
 */
#[CoversClass(\Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController::class)] // @phpstan-ignore-line
final class ExtractSearchFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 测试从实体中提取搜索字段
     */
    public function testExtractSearchFields(): void
    {
        // 创建具体的控制器实现
        // @phpstan-ignore-next-line Testing deprecated class functionality
        $controller = new #[AdminCrud] class extends \Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController {
            public static function getEntityFqcn(): string
            {
                return SearchFieldsTestEntity::class;
            }

            // 使用反射访问私有方法
            /**
             * @param \ReflectionClass<object> $reflection
             * @return string[]
             */
            public function publicExtractSearchFields(\ReflectionClass $reflection): array
            {
                $reflectionMethod = new \ReflectionMethod(parent::class, 'extractSearchFields');
                $reflectionMethod->setAccessible(true);

                return $reflectionMethod->invoke($this, $reflection);
            }
        };

        // 获取实体的反射类
        $entityReflection = new \ReflectionClass(SearchFieldsTestEntity::class);

        // 调用方法
        $result = $controller->publicExtractSearchFields($entityReflection);

        // 验证结果
        $this->assertCount(2, $result);
        $this->assertContains('title', $result);
        $this->assertContains('keywords', $result);
        $this->assertNotContains('description', $result);
    }
}
