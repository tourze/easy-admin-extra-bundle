<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[Autoconfigure(public: true)]
class TextHelper
{
    public function __construct(
        private readonly DocBlockFactoryInterface $docBlockFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    public function getTitleFromReflection(\ReflectionClass $reflectionClass): string
    {
        // 支持自定义标题
        $envKey = 'ADMIN_CURD_PAGE_TITLE_' . $reflectionClass->getName();
        if (isset($_ENV[$envKey])) {
            return strval($_ENV[$envKey]);
        }

        $permission = $reflectionClass->getAttributes(AsPermission::class);
        if ([] !== $permission) {
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
