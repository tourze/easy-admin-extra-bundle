<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Contract;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Contract\CopyableRepository;

/**
 * CopyableRepository 接口的测试类
 *
 * 注意：CopyableRepository 仅包含 copy 方法，不包含 find 或 findAll 方法
 * 因此不需要测试标准 Repository 的 find 相关用例
 *
 * @internal
 */
#[CoversClass(CopyableRepository::class)]
final class CopyableInterfaceTest extends TestCase
{
    public function testCopyImplementationReturnsClonedObject(): void
    {
        // 创建一个具有 id 和 name 属性的测试对象
        $original = new class {
            public ?int $id = 1;

            public string $name = 'Original';
        };

        $repository = new class implements CopyableRepository {
            public function copy(object $object): object
            {
                $copy = clone $object;
                if (property_exists($copy, 'id')) {
                    $copy->id = null;
                }

                return $copy;
            }
        };

        $copied = $repository->copy($original);

        $this->assertNotSame($original, $copied);

        // 使用 property_exists 确保属性存在
        $this->assertTrue(property_exists($copied, 'id'));
        $this->assertTrue(property_exists($copied, 'name'));
        $this->assertNull($copied->id);
        $this->assertEquals('Original', $copied->name);
    }

    public function testCopyImplementationHandlesDifferentObjectTypes(): void
    {
        $repository = new class implements CopyableRepository {
            public function copy(object $object): object
            {
                return clone $object;
            }
        };

        $datetime = new \DateTime('2023-01-01');
        $copiedDatetime = $repository->copy($datetime);

        $this->assertNotSame($datetime, $copiedDatetime);

        // 验证 DateTime 对象的类型和格式方法
        $this->assertInstanceOf(\DateTime::class, $copiedDatetime);
        $this->assertEquals('2023-01-01', $datetime->format('Y-m-d'));
        $this->assertEquals('2023-01-01', $copiedDatetime->format('Y-m-d'));
    }
}
