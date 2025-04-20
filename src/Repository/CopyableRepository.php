<?php

namespace Tourze\EasyAdminExtraBundle\Repository;

interface CopyableRepository
{
    /**
     * 返回一个需要复制的实体对象
     */
    public function copy(object $object): object;
}
