<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use Doctrine\ORM\Mapping as ORM;

/**
 * 测试实体类
 *
 * @internal
 */
class TestImportEntity
{
    #[ORM\Id]
    #[ORM\Column]
    public int $id;

    #[ORM\Column]
    public string $name;

    #[ORM\Column(unique: true)]
    public string $code;

    public string $nonColumnProperty;
}
