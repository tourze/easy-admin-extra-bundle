<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

/**
 * 代理类，用于mock ListColumn属性
 *
 * @internal
 */
class ListColumnProxy
{
    public function __construct(
        public ?string $title = null,
    ) {
    }

    public function __get(string $name): mixed
    {
        return match ($name) {
            'title' => $this->title,
            default => null,
        };
    }
}
