<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

/**
 * 代理类，用于mock Column属性
 *
 * @internal
 */
class ColumnProxy
{
    /**
     * @param array<string, mixed>|null $options
     */
    public function __construct(
        public ?array $options = null,
        public ?string $name = null,
        public ?int $length = null,
        public ?string $type = null,
        public ?bool $unique = false,
    ) {
    }

    public function __get(string $name): mixed
    {
        return match ($name) {
            'options' => $this->options,
            'name' => $this->name,
            'length' => $this->length,
            'type' => $this->type,
            'unique' => $this->unique,
            default => null,
        };
    }
}
