<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\EasyAdminExtraBundle\Event\RecordFormTrait;

/**
 * 测试类，用于测试RecordFormTrait特征
 *
 * @internal
 */
class TestEvent extends Event
{
    use RecordFormTrait;
}
