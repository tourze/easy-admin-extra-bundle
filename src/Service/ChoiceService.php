<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use Tourze\EnumExtra\Labelable;
use Traversable;

class ChoiceService
{
    /**
     * @param class-string<\BackedEnum> $enumType
     */
    public function createChoicesFromEnum(string $enumType): Traversable
    {
        foreach ($enumType::cases() as $case) {
            /** @var \BackedEnum $case */
            if ($case instanceof Labelable) {
                yield $case->getLabel() => $case;
                continue;
            }
            yield $case->name => $case->value;
        }
    }
}
