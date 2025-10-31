<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\EasyAdminExtraBundle\Event\ModifyRecordEvent;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(ModifyRecordEvent::class)]
final class ModifyRecordEventTest extends AbstractEventTestCase
{
    public function testModelGetterAndSetter(): void
    {
        $model = new \stdClass();
        $model->id = 1;
        $model->name = 'Test';

        $event = new ModifyRecordEvent();
        $event->setModel($model);

        self::assertSame($model, $event->getModel());
    }

    public function testFormGetterAndSetter(): void
    {
        $form = ['field1' => 'value1', 'field2' => 'value2'];

        $event = new ModifyRecordEvent();
        $event->setForm($form);

        self::assertSame($form, $event->getForm());
    }

    public function testCompleteWorkflow(): void
    {
        $model = new \stdClass();
        $model->id = 1;

        $form = ['name' => 'Updated Name'];

        $event = new ModifyRecordEvent();
        $event->setModel($model);
        $event->setForm($form);

        self::assertSame($model, $event->getModel());
        self::assertSame($form, $event->getForm());
    }
}
