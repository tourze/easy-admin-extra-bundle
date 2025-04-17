<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\EasyAdminExtraBundle\Event\RecordFormTrait;

/**
 * 测试类，用于测试RecordFormTrait特征
 */
class TestEvent extends Event
{
    use RecordFormTrait;
}

/**
 * 测试RecordFormTrait特征
 */
class RecordFormTraitTest extends TestCase
{
    /**
     * 测试模型属性访问
     */
    public function testModelProperty(): void
    {
        $event = new TestEvent();

        // 创建测试模型对象
        $model = new \stdClass();
        $model->id = 123;
        $model->name = 'Test Object';

        // 设置模型
        $event->setModel($model);

        // 验证获取的模型与设置的一致
        $this->assertSame($model, $event->getModel());

        // 验证模型属性
        $retrievedModel = $event->getModel();
        $this->assertEquals(123, $retrievedModel->id);
        $this->assertEquals('Test Object', $retrievedModel->name);
    }

    /**
     * 测试表单数据属性访问
     */
    public function testFormProperty(): void
    {
        $event = new TestEvent();

        // 创建测试表单数据
        $formData = [
            'id' => 456,
            'name' => 'Form Data',
            'options' => [
                'option1' => true,
                'option2' => false
            ]
        ];

        // 设置表单数据
        $event->setForm($formData);

        // 验证获取的表单数据与设置的一致
        $this->assertSame($formData, $event->getForm());

        // 验证表单数据内容
        $retrievedForm = $event->getForm();
        $this->assertEquals(456, $retrievedForm['id']);
        $this->assertEquals('Form Data', $retrievedForm['name']);
        $this->assertArrayHasKey('options', $retrievedForm);
        $this->assertTrue($retrievedForm['options']['option1']);
        $this->assertFalse($retrievedForm['options']['option2']);
    }

    /**
     * 测试不同模型类型
     */
    public function testDifferentModelTypes(): void
    {
        $event = new TestEvent();

        // 测试各种不同的对象类型
        $model1 = new \stdClass();
        $event->setModel($model1);
        $this->assertSame($model1, $event->getModel());

        // 测试自定义类
        $model2 = new class() {
            public $property = 'value';
        };
        $event->setModel($model2);
        $this->assertSame($model2, $event->getModel());
        $this->assertEquals('value', $event->getModel()->property);
    }

    /**
     * 测试复杂表单数据
     */
    public function testComplexFormData(): void
    {
        $event = new TestEvent();

        // 创建复杂嵌套的表单数据
        $complexFormData = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep value'
                ]
            ],
            'list' => [1, 2, 3, 4, 5],
            'mixed' => [
                'string' => 'text',
                'number' => 42,
                'boolean' => true,
                'null' => null
            ]
        ];

        // 设置表单数据
        $event->setForm($complexFormData);

        // 验证复杂嵌套数据
        $retrievedForm = $event->getForm();
        $this->assertEquals('deep value', $retrievedForm['level1']['level2']['level3']);
        $this->assertEquals([1, 2, 3, 4, 5], $retrievedForm['list']);
        $this->assertEquals('text', $retrievedForm['mixed']['string']);
        $this->assertEquals(42, $retrievedForm['mixed']['number']);
        $this->assertTrue($retrievedForm['mixed']['boolean']);
        $this->assertNull($retrievedForm['mixed']['null']);
    }
}
