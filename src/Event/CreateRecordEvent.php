<?php

namespace Tourze\EasyAdminExtraBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CreateRecordEvent extends Event
{
    use RecordFormTrait;
}
