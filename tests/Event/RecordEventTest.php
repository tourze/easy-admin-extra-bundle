<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Event\CreateRecordEvent;
use Tourze\EasyAdminExtraBundle\Event\ModifyRecordEvent;

/**
 * 测试记录事件类
 */
class RecordEventTest extends TestCase
{
    /**
     * 测试CreateRecordEvent实例化和属性访问
     */
    public function testCreateRecordEvent(): void
    {
        // 创建一个测试模型对象
        $model = new \stdClass();
        $model->id = 1;
        $model->name = 'Test Model';

        // 创建一个表单数据数组
        $formData = [
            'id' => 1,
            'name' => 'Test Model',
            'description' => 'This is a test model'
        ];

        // 创建事件实例
        $event = new CreateRecordEvent();

        // 设置模型和表单数据
        $event->setModel($model);
        $event->setForm($formData);

        // 测试getter方法
        $this->assertSame($model, $event->getModel());
        $this->assertSame($formData, $event->getForm());

        // 验证事件属性
        $this->assertObjectHasProperty('id', $event->getModel());
        $this->assertObjectHasProperty('name', $event->getModel());
        $this->assertEquals(1, $event->getModel()->id);
        $this->assertEquals('Test Model', $event->getModel()->name);

        // 验证表单数据
        $this->assertArrayHasKey('id', $event->getForm());
        $this->assertArrayHasKey('name', $event->getForm());
        $this->assertArrayHasKey('description', $event->getForm());
        $this->assertEquals(1, $event->getForm()['id']);
        $this->assertEquals('Test Model', $event->getForm()['name']);
        $this->assertEquals('This is a test model', $event->getForm()['description']);
    }

    /**
     * 测试ModifyRecordEvent实例化和属性访问
     */
    public function testModifyRecordEvent(): void
    {
        // 创建一个测试模型对象
        $model = new \stdClass();
        $model->id = 2;
        $model->name = 'Updated Model';

        // 创建一个表单数据数组
        $formData = [
            'id' => 2,
            'name' => 'Updated Model',
            'description' => 'This model has been updated'
        ];

        // 创建事件实例
        $event = new ModifyRecordEvent();

        // 设置模型和表单数据
        $event->setModel($model);
        $event->setForm($formData);

        // 测试getter方法
        $this->assertSame($model, $event->getModel());
        $this->assertSame($formData, $event->getForm());

        // 验证事件属性
        $this->assertObjectHasProperty('id', $event->getModel());
        $this->assertObjectHasProperty('name', $event->getModel());
        $this->assertEquals(2, $event->getModel()->id);
        $this->assertEquals('Updated Model', $event->getModel()->name);

        // 验证表单数据
        $this->assertArrayHasKey('id', $event->getForm());
        $this->assertArrayHasKey('name', $event->getForm());
        $this->assertArrayHasKey('description', $event->getForm());
        $this->assertEquals(2, $event->getForm()['id']);
        $this->assertEquals('Updated Model', $event->getForm()['name']);
        $this->assertEquals('This model has been updated', $event->getForm()['description']);
    }

    /**
     * 测试事件的类继承关系
     */
    public function testEventInheritance(): void
    {
        $createEvent = new CreateRecordEvent();
        $modifyEvent = new ModifyRecordEvent();

        // 验证事件继承自Symfony Event类
        $this->assertInstanceOf('Symfony\Contracts\EventDispatcher\Event', $createEvent);
        $this->assertInstanceOf('Symfony\Contracts\EventDispatcher\Event', $modifyEvent);
    }
}
