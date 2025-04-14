<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\ChoiceService;
use Tourze\EnumExtra\Labelable;
use Traversable;

/**
 * 测试状态枚举
 */
enum TestStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}

/**
 * 带有标签的测试状态枚举
 */
enum TestStatusWithLabelEnum: string implements Labelable
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * 获取标签
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => '启用',
            self::INACTIVE => '禁用',
        };
    }
}

class ChoiceServiceTest extends TestCase
{
    private ChoiceService $choiceService;

    protected function setUp(): void
    {
        $this->choiceService = new ChoiceService();
    }

    /**
     * 测试从普通枚举创建选项
     */
    public function testCreateChoicesFromNormalEnum(): void
    {
        $choices = $this->choiceService->createChoicesFromEnum(TestStatusEnum::class);

        $this->assertInstanceOf(Traversable::class, $choices);

        $choicesArray = iterator_to_array($choices);

        $this->assertCount(2, $choicesArray);
        $this->assertArrayHasKey('ACTIVE', $choicesArray);
        $this->assertArrayHasKey('INACTIVE', $choicesArray);
        $this->assertEquals('active', $choicesArray['ACTIVE']);
        $this->assertEquals('inactive', $choicesArray['INACTIVE']);
    }

    /**
     * 测试从实现了Labelable接口的枚举创建选项
     */
    public function testCreateChoicesFromLabelableEnum(): void
    {
        $choices = $this->choiceService->createChoicesFromEnum(TestStatusWithLabelEnum::class);

        $this->assertInstanceOf(Traversable::class, $choices);

        $choicesArray = iterator_to_array($choices);

        $this->assertCount(2, $choicesArray);
        $this->assertArrayHasKey('启用', $choicesArray);
        $this->assertArrayHasKey('禁用', $choicesArray);
        $this->assertEquals(TestStatusWithLabelEnum::ACTIVE, $choicesArray['启用']);
        $this->assertEquals(TestStatusWithLabelEnum::INACTIVE, $choicesArray['禁用']);
    }
}
