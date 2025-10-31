<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Method;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdmin\Attribute\Field\FormField;

/**
 * 测试字段配置功能
 *
 * @internal
 */
/**
 * @phpstan-ignore-next-line Testing deprecated class functionality
 * @internal
 */
#[CoversClass(\Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController::class)] // @phpstan-ignore-line
final class FieldsConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 测试字段配置和排序
     */
    public function testConfigureFields(): void
    {
        // 这是一个简单的占位测试，验证OrderedFieldEntity实体的字段顺序属性
        $entityReflection = new \ReflectionClass(OrderedFieldEntity::class);
        $properties = $entityReflection->getProperties();

        // 验证实体有预期的属性
        $this->assertCount(3, $properties);

        // 验证属性名称
        $propertyNames = array_map(fn ($p) => $p->getName(), $properties);
        $this->assertContains('name', $propertyNames);
        $this->assertContains('title', $propertyNames);
        $this->assertContains('description', $propertyNames);

        // 验证FormField注解存在
        $titleProperty = $entityReflection->getProperty('title');
        $titleFormFieldAttrs = $titleProperty->getAttributes(FormField::class);
        $this->assertCount(1, $titleFormFieldAttrs);

        // 验证FormField注解的order属性
        $titleFormField = $titleFormFieldAttrs[0]->newInstance();
        $this->assertEquals(1, $titleFormField->order);
    }
}
