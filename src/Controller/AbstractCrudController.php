<?php

namespace Tourze\EasyAdminExtraBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\SearchMode;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Tourze\EasyAdmin\Attribute\Action\BatchDeletable;
use Tourze\EasyAdmin\Attribute\Action\Copyable;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\CurdAction;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\CopyColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdminExtraBundle\Service\FieldService;
use Tourze\EasyAdminExtraBundle\Service\FilterService;
use Tourze\EasyAdminExtraBundle\Service\TextHelper;

/**
 * 继承，做一些通用逻辑处理
 *
 * @deprecated 直接改为继承 \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController
 * @template TEntity of object
 * @extends \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController<TEntity>
 */
abstract class AbstractCrudController extends \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            LoggerInterface::class => '?' . LoggerInterface::class,
            AdminUrlGenerator::class => '?' . AdminUrlGenerator::class,
            TextHelper::class => '?' . TextHelper::class,
            FilterService::class => '?' . FilterService::class,
            FieldService::class => '?' . FieldService::class,
        ]);
    }

    protected function getTextHelper(): TextHelper
    {
        return $this->container->get(TextHelper::class);
    }

    private function getLogger(): LoggerInterface
    {
        return $this->container->get(LoggerInterface::class);
    }

    protected function getAdminUrlGenerator(): AdminUrlGenerator
    {
        return $this->container->get(AdminUrlGenerator::class);
    }

    protected function getFilterService(): FilterService
    {
        return $this->container->get(FilterService::class);
    }

    protected function getFieldService(): FieldService
    {
        return $this->container->get(FieldService::class);
    }

    /**
     * @return \ReflectionClass<TEntity>
     */
    public function getEntityReflection(): \ReflectionClass
    {
        return new \ReflectionClass(static::getEntityFqcn());
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $reflection = $this->getEntityReflection();

        // 标题
        $title = $this->getTextHelper()->getTitleFromReflection($reflection);
        $crud->setEntityLabelInSingular($title)
            ->setEntityLabelInPlural($title)
        ;

        // 权限控制
        $crud->setEntityPermission(static::getEntityFqcn() . '::list');

        // 搜索的支持
        $searchFields = $this->extractSearchFields($reflection);
        $crud->setSearchFields([] !== $searchFields ? $searchFields : null)
            ->setSearchMode(SearchMode::ANY_TERMS)
        ;

        // 默认排序
        $crud->setDefaultSort(['id' => 'DESC']);

        return $crud;
    }

    /**
     * 从类的反射对象中读取可以用来关键词搜索的内容
     * @param \ReflectionClass<TEntity> $reflection
     * @return string[]
     */
    private function extractSearchFields(\ReflectionClass $reflection): array
    {
        $fields = [];
        foreach ($reflection->getProperties() as $property) {
            if ([] === $property->getAttributes(Keyword::class)) {
                continue;
            }
            $fields[] = $property->getName();
        }

        return $fields;
    }

    /**
     * 有一些情景，我们需要读取上级信息，那么可以通过这个来做
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $entityQuery = $searchDto->getRequest()->query->all()['entity'] ?? null;
        if (is_array($entityQuery)) {
            foreach ($entityQuery as $k => $v) {
                // 检查字段是否合法：通过EntityDto获取字段元数据进行验证
                if (!$entityDto->hasProperty($k)) {
                    continue; // 跳过不存在的字段
                }

                $placeholder = 'e' . md5($k);
                $qb->andWhere(sprintf('entity.%s = :%s', $k, $placeholder));
                $qb->setParameter($placeholder, $v);
            }
        }

        return $qb;
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        // 默认提交后，跳转到列表
        $submitButtonName = $context->getRequest()->request->all()['ea']['newForm']['btn'] ?? null;
        if (Action::SAVE_AND_RETURN === $submitButtonName) {
            $url = $this->getAdminUrlGenerator()->setAction(Action::INDEX)->generateUrl();

            return $this->redirect($url);
        }

        return parent::getRedirectResponseAfterSave($context, $action);
    }

    public function configureFields(string $pageName): iterable
    {
        $reflection = $this->getEntityReflection();
        $properties = $reflection->getProperties();

        // 收集所有字段及其排序信息
        $fields = [];
        $currentOrder = 0; // 当前序号，从0开始

        foreach ($properties as $property) {
            $field = $this->getFieldService()->createFieldFromProperty($property, $pageName);
            if (null === $field) {
                continue;
            }

            $this->getLogger()->debug('创建CURD Field', [
                'property' => $property,
                'field' => $field,
            ]);

            // 获取注解中的排序值
            $annotationOrder = $this->getFieldOrder($property, $pageName);

            // 如果注解中没有指定order，就使用当前序号
            $order = $annotationOrder ?? $currentOrder;

            $fields[] = [
                'field' => $field,
                'order' => $order,
                'propertyName' => $property->getName(),
            ];

            ++$currentOrder;
        }

        // 根据order排序
        usort($fields, function ($a, $b) {
            // 直接比较order值，因为现在每个字段都有order值了
            return $a['order'] <=> $b['order'];
        });

        // 返回排序后的字段
        foreach ($fields as $fieldInfo) {
            yield $fieldInfo['field'];
        }
    }

    private function getFieldOrder(\ReflectionProperty $property, string $pageName): ?int
    {
        // 处理 ListColumn 注解
        $listColumnAttr = $property->getAttributes(ListColumn::class)[0] ?? null;
        $listColumn = $listColumnAttr?->newInstance();

        // 处理 FormField 注解
        $formFieldAttr = $property->getAttributes(FormField::class)[0] ?? null;
        $formField = $formFieldAttr?->newInstance();

        // 根据页面类型返回对应的order，如果没有设置则返回null
        return match ($pageName) {
            Crud::PAGE_INDEX, Crud::PAGE_DETAIL => $listColumn?->order,
            Crud::PAGE_NEW, Crud::PAGE_EDIT => $formField?->order,
            default => null,
        };
    }

    public function configureFilters(Filters $filters): Filters
    {
        $reflection = $this->getEntityReflection();
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $filter = $this->getFilterService()->createFilterFromProperty($property);
            if (null !== $filter) {
                $this->getLogger()->debug('创建CURD Filter', [
                    'property' => $property,
                    'filter' => $filter,
                ]);
                $filters->add($filter);
            }
        }

        return $filters;
    }

    public function configureActions(Actions $actions): Actions
    {
        $reflection = $this->getEntityReflection();

        $this->addBasicActions($actions);
        $this->addAttributeBasedActions($actions, $reflection);
        $this->addPropertyBasedActions($actions, $reflection);

        return $actions;
    }

    private function addBasicActions(Actions $actions): void
    {
        if (null === $actions->getAsDto(Crud::PAGE_DETAIL)->getAction(Crud::PAGE_DETAIL, Action::INDEX)) {
            $actions->add(Crud::PAGE_DETAIL, Action::INDEX);
        }
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private function addAttributeBasedActions(Actions $actions, \ReflectionClass $reflection): void
    {
        $this->addBatchActions($actions, $reflection);
        $this->addCrudActions($actions, $reflection);
        $this->addCopyActions($actions, $reflection);
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private function addBatchActions(Actions $actions, \ReflectionClass $reflection): void
    {
        if ([] !== $reflection->getAttributes(BatchDeletable::class)) {
            $actions->addBatchAction(Action::BATCH_DELETE);
        }
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private function addCrudActions(Actions $actions, \ReflectionClass $reflection): void
    {
        if ([] !== $reflection->getAttributes(Creatable::class)) {
            $actions->add(Crud::PAGE_INDEX, Action::NEW);
            $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_RETURN);
            $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
        }

        if ([] !== $reflection->getAttributes(Editable::class)) {
            $actions->add(Crud::PAGE_INDEX, Action::EDIT);
            $actions->add(Crud::PAGE_DETAIL, Action::EDIT);
            $actions->add(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);
            $actions->add(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
        }

        if ([] !== $reflection->getAttributes(Deletable::class)) {
            $actions->add(Crud::PAGE_INDEX, Action::DELETE);
            $actions->add(Crud::PAGE_DETAIL, Action::DELETE);
        }
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private function addCopyActions(Actions $actions, \ReflectionClass $reflection): void
    {
        if ([] !== $reflection->getAttributes(Copyable::class)) {
            $copyAction = Action::new('copy', '复制')
                ->linkToCrudAction('copyAction')
            ;

            $actions->add(Crud::PAGE_INDEX, $copyAction);
            $actions->add(Crud::PAGE_DETAIL, $copyAction);
        }
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private function addPropertyBasedActions(Actions $actions, \ReflectionClass $reflection): void
    {
        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes(CurdAction::class) as $curdAction) {
                $this->addCurdAction($actions, $property, $curdAction->newInstance());
            }
        }
    }

    private function addCurdAction(Actions $actions, \ReflectionProperty $property, CurdAction $curdAction): void
    {
        $action = Action::new('CurdAction-' . $property->getName(), $curdAction->label);
        $action->displayAsLink();
        $action->linkToUrl(function (object $value) use ($property) {
            $oneToMany = $property->getAttributes(ORM\OneToMany::class)[0]->newInstance();
            $controller = $this->container->get(AdminContextProvider::class)
                ->getCrudControllers()
                ->findCrudFqcnByEntityFqcn($oneToMany->targetEntity)
            ;

            return $this->getAdminUrlGenerator()
                ->setController($controller)
                ->set('entity', [
                    $oneToMany->mappedBy => method_exists($value, 'getId') ? $value->getId() : null,
                ])
                ->generateUrl()
            ;
        });
        $actions->add(Crud::PAGE_INDEX, $action);
    }

    public function delete(AdminContext $context): Response
    {
        $response = parent::delete($context);
        if ($response instanceof RedirectResponse) {
            $this->addFlash('success', '删除成功');
        }

        // 确保返回类型为Response
        return $response instanceof Response ? $response : new Response();
    }

    #[AdminAction(routePath: '{entityId}/copy', routeName: 'copy')]
    public function copyAction(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $entityInstance = $context->getEntity()->getInstance();
        if (null === $entityInstance) {
            return $this->createErrorResponse('无法获取实体实例');
        }
        $reflection = new \ReflectionClass($entityInstance);

        if (!$this->isEntityCopyable($reflection)) {
            return $this->createErrorResponse('该实体不支持复制功能');
        }

        $newEntityInstance = $this->createCopiedEntity($entityInstance, $reflection);

        return $this->persistCopiedEntity($newEntityInstance, $entityManager);
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private function isEntityCopyable(\ReflectionClass $reflection): bool
    {
        return [] !== $reflection->getAttributes(Copyable::class);
    }

    private function createErrorResponse(string $message): Response
    {
        $this->addFlash('danger', $message);

        return $this->redirect($this->getAdminUrlGenerator()
            ->setController(get_class($this))
            ->setAction(Action::INDEX)
            ->generateUrl());
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private function createCopiedEntity(object $originalEntity, \ReflectionClass $reflection): object
    {
        $entityFqcn = get_class($originalEntity);
        $newEntityInstance = new $entityFqcn();

        foreach ($reflection->getProperties() as $property) {
            $this->copyPropertyIfCopyable($property, $originalEntity, $newEntityInstance);
        }

        return $newEntityInstance;
    }

    private function copyPropertyIfCopyable(\ReflectionProperty $property, object $originalEntity, object $newEntity): void
    {
        $copyColumnAttr = $property->getAttributes(CopyColumn::class)[0] ?? null;
        if (null === $copyColumnAttr) {
            return;
        }

        $copyColumn = $copyColumnAttr->newInstance();

        if (null !== $copyColumn->fixedValue) {
            $property->setValue($newEntity, $copyColumn->fixedValue);

            return;
        }

        $originalValue = $property->getValue($originalEntity);
        $copiedValue = $this->processCopyValue($originalValue, $copyColumn);
        $property->setValue($newEntity, $copiedValue);
    }

    private function processCopyValue(mixed $originalValue, CopyColumn $copyColumn): mixed
    {
        if (false === $copyColumn->suffix || !is_string($originalValue)) {
            return $originalValue;
        }

        $suffix = true === $copyColumn->suffix ? ' - 副本' : $copyColumn->suffix;

        return $originalValue . $suffix;
    }

    private function persistCopiedEntity(object $newEntityInstance, EntityManagerInterface $entityManager): Response
    {
        try {
            $entityManager->persist($newEntityInstance);
            $entityManager->flush();
            $this->addFlash('success', '复制成功');
        } catch (\Throwable $exception) {
            $this->getLogger()->error('复制实体时发生错误', [
                'entityInstance' => $newEntityInstance,
                'exception' => $exception,
            ]);
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirect($this->getAdminUrlGenerator()
            ->setController(get_class($this))
            ->setAction(Action::INDEX)
            ->generateUrl());
    }
}
