<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\ColumnService;

/**
 * @internal
 */
#[CoversClass(ColumnService::class)]
final class ColumnServiceTest extends TestCase
{
    private ColumnService $columnService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnService = new ColumnService();
    }

    /**
     * 测试格式化枚举列
     */
    public function testFormatEnumColumn(): void
    {
        // 准备一个枚举类型的属性
        $reflectionClass = new \ReflectionClass(TestEntity::class);
        $enumProperty = $reflectionClass->getProperty('enumProperty');

        // 测试格式化枚举列
        $result = $this->columnService->formatEnumColumn($enumProperty, '枚举属性');

        // 验证结果
        $this->assertEquals('枚举属性', $result['title']);
        $this->assertEquals(TestEnum::class, $result['className']);
        $this->assertTrue($result['isMatch']);

        // 测试非枚举类型的属性
        $normalProperty = $reflectionClass->getProperty('normalProperty');
        $result = $this->columnService->formatEnumColumn($normalProperty, '普通属性');

        // 验证结果
        $this->assertEquals('普通属性', $result['title']);
        $this->assertEquals('', $result['className']);
        $this->assertFalse($result['isMatch']);
    }
}
