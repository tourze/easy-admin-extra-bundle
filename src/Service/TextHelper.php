<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlockFactory;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

class TextHelper
{
    public function __construct(
        private readonly object $docBlockFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getTitleFromReflection(\ReflectionClass $reflectionClass): string
    {
        // 支持自定义标题
        $envKey = 'ADMIN_CURD_PAGE_TITLE_' . $reflectionClass->getName();
        if (isset($_ENV[$envKey])) {
            return strval($_ENV[$envKey]);
        }

        $permission = $reflectionClass->getAttributes(AsPermission::class);
        if (!empty($permission)) {
            $permission = $permission[0]->newInstance();

            /* @var AsPermission $permission */
            return $permission->title;
        }

        $comment = $reflectionClass->getDocComment();
        if (false === $comment) {
            return '';
        }

        $docblock = $this->docBlockFactory->create($comment);

        return $docblock->getSummary();
    }

    public function getTitleFromClassName(string $className): string
    {
        return $this->getTitleFromReflection($this->entityManager->getClassMetadata($className)->getReflectionClass());
    }
}
