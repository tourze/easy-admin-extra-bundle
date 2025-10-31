<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\ChoiceService;

/**
 * @internal
 */
#[CoversClass(ChoiceService::class)]
final class ChoiceServiceTest extends TestCase
{
    private ChoiceService $choiceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->choiceService = new ChoiceService();
    }

    /**
     * 测试从普通枚举创建选项
     */
    public function testCreateChoicesFromEnum(): void
    {
        $choices = $this->choiceService->createChoicesFromEnum(TestStatusEnum::class);

        $this->assertInstanceOf(\Traversable::class, $choices);

        $choicesArray = iterator_to_array($choices);

        $this->assertCount(2, $choicesArray);
        $this->assertArrayHasKey('ACTIVE', $choicesArray);
        $this->assertArrayHasKey('INACTIVE', $choicesArray);
        $this->assertEquals(TestStatusEnum::ACTIVE, $choicesArray['ACTIVE']);
        $this->assertEquals(TestStatusEnum::INACTIVE, $choicesArray['INACTIVE']);
    }

    /**
     * 测试从实现了Labelable接口的枚举创建选项
     */
    public function testCreateChoicesFromLabelableEnum(): void
    {
        $choices = $this->choiceService->createChoicesFromEnum(TestStatusWithLabelEnum::class);

        $this->assertInstanceOf(\Traversable::class, $choices);

        $choicesArray = iterator_to_array($choices);

        $this->assertCount(2, $choicesArray);
        $this->assertArrayHasKey('启用', $choicesArray);
        $this->assertArrayHasKey('禁用', $choicesArray);
        $this->assertEquals(TestStatusWithLabelEnum::ACTIVE, $choicesArray['启用']);
        $this->assertEquals(TestStatusWithLabelEnum::INACTIVE, $choicesArray['禁用']);
    }
}
