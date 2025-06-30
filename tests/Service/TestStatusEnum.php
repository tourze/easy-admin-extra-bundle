<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 测试状态枚举
 * @internal
 */
enum TestStatusEnum: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'ACTIVE',
            self::INACTIVE => 'INACTIVE',
        };
    }
}