<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Method;

use Tourze\EasyAdmin\Attribute\Filter\Keyword;

/**
 * 带有Keyword注解的测试实体类
 *
 * @internal
 */
class SearchFieldsTestEntity
{
    public function __construct(
        #[Keyword]
        private readonly string $title = '',
        private readonly string $description = '',
        #[Keyword]
        private readonly string $keywords = '',
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }
}
