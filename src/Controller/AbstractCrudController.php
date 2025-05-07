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
 */
abstract class AbstractCrudController extends \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            LoggerInterface::class => '?'.LoggerInterface::class,
            AdminUrlGenerator::class => '?'.AdminUrlGenerator::class,
            TextHelper::class => '?'.TextHelper::class,
            FilterService::class => '?'.FilterService::class,
            FieldService::class => '?'.FieldService::class,
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
            ->setEntityLabelInPlural($title);

        // 权限控制
        $crud->setEntityPermission(static::getEntityFqcn() . '::list');

        // 搜索的支持
        $searchFields = $this->extractSearchFields($reflection);
        $crud->setSearchFields($searchFields ?: null)
            ->setSearchMode(SearchMode::ANY_TERMS);

        // 默认排序
        $crud->setDefaultSort(['id' => 'DESC']);

        return $crud;
    }

    /**
     * 从类的反射对象中读取可以用来关键词搜索的内容
     */
    private function extractSearchFields(\ReflectionClass $reflection): array
    {
        $fields = [];
        foreach ($reflection->getProperties() as $property) {
            if (empty($property->getAttributes(Keyword::class))) {
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
            if (!$field) {
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

            $currentOrder++;
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
        return match($pageName) {
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
            $filter = $this->getFilterService()->createFilterFomProperty($property);
            if ($filter) {
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

        $actions->add(Crud::PAGE_DETAIL, Action::INDEX);

        if (!empty($reflection->getAttributes(BatchDeletable::class))) {
            $actions->addBatchAction(Action::BATCH_DELETE);
        }

        if (!empty($reflection->getAttributes(Creatable::class))) {
            $actions->add(Crud::PAGE_INDEX, Action::NEW);
            $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_RETURN);
            $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
        }

        if (!empty($reflection->getAttributes(Editable::class))) {
            $actions->add(Crud::PAGE_INDEX, Action::EDIT);
            $actions->add(Crud::PAGE_DETAIL, Action::EDIT);
            $actions->add(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);
            $actions->add(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
        }

        if (!empty($reflection->getAttributes(Deletable::class))) {
            $actions->add(Crud::PAGE_INDEX, Action::DELETE);
            $actions->add(Crud::PAGE_DETAIL, Action::DELETE);
        }

        if (!empty($reflection->getAttributes(Copyable::class))) {
            $copyAction = Action::new('copy', '复制')
                ->linkToCrudAction('copyAction');

            $actions->add(Crud::PAGE_INDEX, $copyAction);
            $actions->add(Crud::PAGE_DETAIL, $copyAction);
        }

        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes(CurdAction::class) as $curdAction) {
                $curdAction = $curdAction->newInstance();
                /** @var CurdAction $curdAction */
                $action = Action::new('CurdAction-' . $property->getName(), $curdAction->label);
                $action->displayAsLink();
                $action->linkToUrl(function (object $value) use ($property) {
                    $oneToMany = $property->getAttributes(ORM\OneToMany::class)[0]->newInstance();
                    /** @var ORM\OneToMany $oneToMany */

                    $controller = $this->container->get(AdminContextProvider::class)
                        ->getCrudControllers()
                        ->findCrudFqcnByEntityFqcn($oneToMany->targetEntity);
                    return $this->getAdminUrlGenerator()
                        ->setController($controller)
                        ->set('entity', [
                            $oneToMany->mappedBy => $value->getId(),
                        ])
                        ->generateUrl();
                });
                $actions->add(Crud::PAGE_INDEX, $action);
            }
        }

        return $actions;
    }

    public function delete(AdminContext $context): Response
    {
        $response = parent::delete($context);
        if ($response instanceof RedirectResponse) {
            $this->addFlash('success', '删除成功');
        }
        return $response;
    }

    #[AdminAction('{entityId}/copy', 'copy')]
    public function copyAction(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $entityInstance = $context->getEntity()->getInstance();
        $entityFqcn = get_class($entityInstance);
        $reflection = new \ReflectionClass($entityFqcn);

        // 检查实体是否有 @Copyable 注解
        $copyableAttr = $reflection->getAttributes(Copyable::class)[0] ?? null;
        if (!$copyableAttr) {
            $this->addFlash('danger', '该实体不支持复制功能');
            return $this->redirect($this->getAdminUrlGenerator()
                ->setController(static::class)
                ->setAction(Action::INDEX)
                ->generateUrl());
        }

        // 创建新实体实例
        $newEntityInstance = new $entityFqcn();

        // 遍历所有属性，查找带有 @CopyColumn 注解的属性
        foreach ($reflection->getProperties() as $property) {
            $copyColumnAttr = $property->getAttributes(CopyColumn::class)[0] ?? null;
            if (!$copyColumnAttr) {
                continue;
            }

            $copyColumn = $copyColumnAttr->newInstance();
            /** @var CopyColumn $copyColumn */

            // 如果设置了固定值，则使用固定值
            if ($copyColumn->fixedValue !== null) {
                $property->setValue($newEntityInstance, $copyColumn->fixedValue);
                continue;
            }

            // 获取原始值
            $originalValue = $property->getValue($entityInstance);

            // 处理后缀
            if ($copyColumn->suffix !== false && is_string($originalValue)) {
                // 如果suffix是true，使用默认后缀 " - 副本"
                $suffix = $copyColumn->suffix === true ? ' - 副本' : $copyColumn->suffix;
                $property->setValue($newEntityInstance, $originalValue . $suffix);
            } else {
                // 直接复制原始值
                $property->setValue($newEntityInstance, $originalValue);
            }
        }

        try {
            // 保存新实体
            $entityManager->persist($newEntityInstance);
            $entityManager->flush();

            $this->addFlash('success', '复制成功');
        } catch (\Throwable $exception) {
            $this->getLogger()->error('复制实体时发生错误', [
                'entityInstance' => $entityInstance,
                'exception' => $exception,
            ]);
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirect($this->getAdminUrlGenerator()
            ->setController(static::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }
}
