<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\ColumnService;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 测试枚举类型
 */
enum TestEnum: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case ONE = 'one';
    case TWO = 'two';

    public function getLabel(): string
    {
        return match ($this) {
            self::ONE => 'One',
            self::TWO => 'Two',
        };
    }
}

/**
 * 测试实体类
 */
class TestEntity
{
    #[ORM\Column(enumType: TestEnum::class)]
    public TestEnum $enumProperty;

    #[ORM\Column]
    public string $normalProperty;
}

class ColumnServiceTest extends TestCase
{
    private ColumnService $columnService;

    protected function setUp(): void
    {
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
