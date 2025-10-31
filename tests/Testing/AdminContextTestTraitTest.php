<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Testing;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Context\AdminContextInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\EasyAdminExtraBundle\Testing\AdminContextTestTrait;

/**
 * 示例：如何使用 AdminContextTestTrait 解决 EasyAdmin 测试问题
 *
 * @internal
 */
#[CoversClass(AdminContextTestTrait::class)]
final class AdminContextTestTraitTest extends TestCase
{
    use AdminContextTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // 这个测试类不需要特殊的设置
    }

    public function testCreateAdminContextMock(): void
    {
        // 创建一个示例实体
        $entity = new class {
            private int $id = 0;

            private string $name = 'test';

            public function getId(): int
            {
                return $this->id;
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function setName(string $name): void
            {
                $this->name = $name;
            }
        };

        // 设置实体 ID
        $this->setEntityId($entity, 123);
        $this->assertSame(123, $entity->getId());

        // 创建 AdminContext mock
        $adminContext = $this->createAdminContextMock($entity);

        // 验证 mock 对象
        $this->assertInstanceOf(AdminContextInterface::class, $adminContext);
        $this->assertSame($entity, $adminContext->getEntity()->getInstance());
        $this->assertInstanceOf(Request::class, $adminContext->getRequest());
    }
}
