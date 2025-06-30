<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use Doctrine\ORM\Mapping as ORM;

/**
 * 测试实体类
 * @internal
 */
class TestEntity
{
    #[ORM\Column(enumType: TestEnum::class)]
    public TestEnum $enumProperty;

    #[ORM\Column]
    public string $normalProperty;
}