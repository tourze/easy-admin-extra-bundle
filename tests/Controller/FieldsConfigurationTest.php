<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController;
use Tourze\EasyAdminExtraBundle\Service\FieldService;
use Tourze\EasyAdminExtraBundle\Service\FilterService;
use Tourze\EasyAdminExtraBundle\Service\TextHelper;

/**
 * 带有字段排序注解的测试实体类
 * @internal
 */
class OrderedFieldEntity
{
    #[ListColumn(order: 2)]
    #[FormField(order: 2)]
    private string $name;

    #[ListColumn(order: 1)]
    #[FormField(order: 1)]
    private string $title;

    #[ListColumn(order: 3)]
    #[FormField(order: 3)]
    private string $description;

    public function __construct(string $name = '', string $title = '', string $description = '')
    {
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}

/**
 * 测试字段配置功能
 */
class FieldsConfigurationTest extends TestCase
{
    private MockObject|ContainerInterface $container;
    private MockObject|AbstractCrudController $controller;
    private MockObject|FieldService $fieldService;
    private MockObject|FilterService $filterService;
    private MockObject|LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->fieldService = $this->createMock(FieldService::class);
        $this->filterService = $this->createMock(FilterService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        // 配置container mock
        $this->container->method('get')
            ->willReturnCallback(function ($id) {
                return match ($id) {
                    FieldService::class => $this->fieldService,
                    FilterService::class => $this->filterService,
                    LoggerInterface::class => $this->logger,
                    TextHelper::class => $this->createMock(TextHelper::class),
                    default => null,
                };
            });

        // 创建一个模拟的AbstractCrudController
        $this->controller = $this->getMockForAbstractClass(
            AbstractCrudController::class,
            [],
            '',
            true,
            true,
            true,
            ['getEntityReflection']
        );

        // 设置container属性
        $reflection = new \ReflectionClass($this->controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($this->controller, $this->container);
    }

    /**
     * 测试字段配置和排序
     */
    public function testConfigureFields(): void
    {
        // 设置getEntityReflection方法返回OrderedFieldEntity的反射
        $entityReflection = new \ReflectionClass(OrderedFieldEntity::class);
        $this->controller->method('getEntityReflection')
            ->willReturn($entityReflection);

        // 配置fieldService的行为
        $titleField = TextField::new('title');
        $nameField = TextField::new('name');

        $this->fieldService->method('createFieldFromProperty')
            ->willReturnCallback(function ($property, $pageName) use ($titleField, $nameField) {
                if ($property->getName() === 'title') {
                    return $titleField;
                } elseif ($property->getName() === 'name') {
                    return $nameField;
                }
                return null;
            });

        // 通过反射调用configureFields方法
        $method = new \ReflectionMethod($this->controller, 'configureFields');
        $result = $method->invoke($this->controller, Crud::PAGE_INDEX);

        // 将迭代器转换为数组
        $fields = iterator_to_array($result);

        // 验证字段数量和顺序
        $this->assertCount(2, $fields);
        $this->assertSame($titleField, $fields[0]);
        $this->assertSame($nameField, $fields[1]);
    }

}
