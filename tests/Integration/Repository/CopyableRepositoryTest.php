<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Repository\CopyableRepository;

/**
 * CopyableRepository 接口的测试类
 */
class CopyableRepositoryTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(CopyableRepository::class));
    }

    public function testInterfaceHasCopyMethod(): void
    {
        $reflection = new \ReflectionClass(CopyableRepository::class);
        $this->assertTrue($reflection->hasMethod('copy'));
        
        $method = $reflection->getMethod('copy');
        $this->assertTrue($method->isPublic());
        $this->assertEquals(1, $method->getNumberOfRequiredParameters());
    }
}