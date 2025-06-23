<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController;

/**
 * 带有Keyword注解的测试实体类
 */
class SearchFieldsTestEntity
{
    #[Keyword]
    private string $title;

    private string $description;

    #[Keyword]
    private string $keywords;

    public function __construct(string $title = '', string $description = '', string $keywords = '')
    {
        $this->title = $title;
        $this->description = $description;
        $this->keywords = $keywords;
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

/**
 * 测试搜索字段提取方法
 */
class ExtractSearchFieldsTest extends TestCase
{
    /**
     * 测试从实体中提取搜索字段
     */
    public function testExtractSearchFields(): void
    {
        // 创建一个模拟的AbstractCrudController
        $controller = $this->getMockForAbstractClass(
            AbstractCrudController::class,
            [],
            '',
            true,
            true,
            true,
            ['getEntityFqcn']
        );

        // 让controller返回测试实体的FQCN
        $controller->method('getEntityFqcn')
            ->willReturn(SearchFieldsTestEntity::class);

        // 通过反射获取私有方法extractSearchFields
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('extractSearchFields');
        $method->setAccessible(true);

        // 获取实体的反射类
        $entityReflection = new \ReflectionClass(SearchFieldsTestEntity::class);

        // 调用方法
        $result = $method->invoke($controller, $entityReflection);

        // 验证结果
        $this->assertCount(2, $result);
        $this->assertContains('title', $result);
        $this->assertContains('keywords', $result);
        $this->assertNotContains('description', $result);
    }
}
