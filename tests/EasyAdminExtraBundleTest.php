<?php

namespace Tourze\EasyAdminExtraBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\EasyAdminExtraBundle;

class EasyAdminExtraBundleTest extends TestCase
{
    /**
     * 测试Bundle实例化
     */
    public function testBundleInit(): void
    {
        $bundle = new EasyAdminExtraBundle();
        $this->assertInstanceOf(EasyAdminExtraBundle::class, $bundle);
    }
}
