<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 测试枚举类型
 *
 * @internal
 */
enum TestEnum: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case ONE = 'one';
    case TWO = 'two';

    public function getLabel(): string
    {
        return match ($this) {
            self::ONE => 'One',
            self::TWO => 'Two',
        };
    }
}
