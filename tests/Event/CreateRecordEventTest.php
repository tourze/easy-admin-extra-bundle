<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\EasyAdminExtraBundle\Event\CreateRecordEvent;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(CreateRecordEvent::class)]
final class CreateRecordEventTest extends AbstractEventTestCase
{
    public function testModelGetterAndSetter(): void
    {
        $model = new \stdClass();
        $model->id = 1;
        $model->name = 'Test';

        $event = new CreateRecordEvent();
        $event->setModel($model);

        self::assertSame($model, $event->getModel());
    }

    public function testFormGetterAndSetter(): void
    {
        $form = ['field1' => 'value1', 'field2' => 'value2'];

        $event = new CreateRecordEvent();
        $event->setForm($form);

        self::assertSame($form, $event->getForm());
    }

    public function testCompleteWorkflow(): void
    {
        $model = new \stdClass();
        $model->id = 1;

        $form = ['name' => 'Test Name'];

        $event = new CreateRecordEvent();
        $event->setModel($model);
        $event->setForm($form);

        self::assertSame($model, $event->getModel());
        self::assertSame($form, $event->getForm());
    }
}
