<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 带有标签的测试状态枚举
 *
 * @internal
 */
enum TestStatusWithLabelEnum: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;

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
