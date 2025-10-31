<?php

namespace Tourze\EasyAdminExtraBundle\Testing;

use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Context\AdminContextInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use Symfony\Component\HttpFoundation\Request;
use Tourze\EasyAdminExtraBundle\Exception\BusinessRuleException;

/**
 * 提供 EasyAdmin 测试的辅助方法
 *
 * 解决 AdminContext 和 EntityDto 是 final 类无法 mock 的问题
 */
trait AdminContextTestTrait
{
    /**
     * 创建 AdminContext mock 对象用于测试
     *
     * @param object $entity 实体对象
     * @param string|null $entityClass 实体类名，如果不提供则自动获取
     * @return AdminContextInterface
     */
    protected function createAdminContextMock(object $entity, ?string $entityClass = null): AdminContextInterface
    {
        $entityClass ??= get_class($entity);

        $request = Request::create('/test');
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']);

        /** @var class-string<object> $entityClass */
        $entityDto = new EntityDto($entityClass, $metadata, null, $entity);

        $adminContext = $this->createMock(AdminContextInterface::class);
        $adminContext->method('getEntity')->willReturn($entityDto);
        $adminContext->method('getRequest')->willReturn($request);

        return $adminContext;
    }

    /**
     * 使用反射设置实体的 ID 属性
     *
     * 因为大多数实体的 ID 属性没有公共的 setter 方法
     *
     * @param object $entity 实体对象
     * @param mixed $id ID 值
     * @param string $idProperty ID 属性名，默认为 'id'
     */
    protected function setEntityId(object $entity, mixed $id, string $idProperty = 'id'): void
    {
        $reflection = new \ReflectionClass($entity);

        if (!$reflection->hasProperty($idProperty)) {
            throw new BusinessRuleException(sprintf('Entity %s does not have property %s', get_class($entity), $idProperty));
        }

        $property = $reflection->getProperty($idProperty);
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }
}
