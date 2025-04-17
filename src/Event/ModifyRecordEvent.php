<?php

namespace Tourze\EasyAdminExtraBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ModifyRecordEvent extends Event
{
    use RecordFormTrait;
}
