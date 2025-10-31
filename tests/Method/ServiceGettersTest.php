<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Method;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Tourze\EasyAdminExtraBundle\Service\FieldService;
use Tourze\EasyAdminExtraBundle\Service\FilterService;
use Tourze\EasyAdminExtraBundle\Service\TextHelper;

/**
 * 测试AbstractCrudController中的Service Getter方法
 *
 * @internal
 */
#[CoversClass(\Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController::class)] // @phpstan-ignore-line
final class ServiceGettersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @return \Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController
     * @phpstan-ignore-next-line
     */
    private function createTestController(): \Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController
    {
        /** @phpstan-ignore class.extendsDeprecatedClass */
        return new #[AdminCrud] class extends \Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController {
            public static function getEntityFqcn(): string
            {
                return SearchFieldsTestEntity::class;
            }

            // 使用反射访问保护和私有方法
            public function publicGetTextHelper(): TextHelper
            {
                /** @phpstan-ignore-next-line Testing deprecated class functionality */
                return $this->getTextHelper();
            }

            public function publicGetLogger(): LoggerInterface
            {
                $reflectionMethod = new \ReflectionMethod(parent::class, 'getLogger');
                $reflectionMethod->setAccessible(true);

                return $reflectionMethod->invoke($this);
            }

            public function publicGetFilterService(): FilterService
            {
                /** @phpstan-ignore-next-line Testing deprecated class functionality */
                return $this->getFilterService();
            }

            public function publicGetFieldService(): FieldService
            {
                /** @phpstan-ignore-next-line Testing deprecated class functionality */
                return $this->getFieldService();
            }
        };
    }

    /**
     * 测试getTextHelper方法
     */
    public function testGetTextHelper(): void
    {
        // 创建具体的控制器实现
        $controller = $this->createTestController();

        // 创建模拟的服务
        $textHelper = $this->createMock(TextHelper::class);

        // 创建模拟的Container
        $container = $this->createMock(ContainerInterface::class);

        // 配置Container返回TextHelper服务
        $container->method('get')
            ->with(TextHelper::class)
            ->willReturn($textHelper)
        ;

        // 设置container属性
        $reflection = new \ReflectionClass($controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($controller, $container);

        // 调用公开的测试方法
        /** @phpstan-ignore-next-line */
        $result = $controller->publicGetTextHelper();

        // 验证结果是TextHelper的实例
        $this->assertInstanceOf(TextHelper::class, $result);
    }

    /**
     * 测试getLogger方法
     */
    public function testGetLogger(): void
    {
        // 创建具体的控制器实现
        $controller = $this->createTestController();

        // 创建模拟的服务
        $logger = $this->createMock(LoggerInterface::class);

        // 创建模拟的Container
        $container = $this->createMock(ContainerInterface::class);

        // 配置Container返回Logger服务
        $container->method('get')
            ->with(LoggerInterface::class)
            ->willReturn($logger)
        ;

        // 设置container属性
        $reflection = new \ReflectionClass($controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($controller, $container);

        // 调用公开的测试方法
        /** @phpstan-ignore-next-line */
        $result = $controller->publicGetLogger();

        // 验证结果是LoggerInterface的实例
        $this->assertInstanceOf(LoggerInterface::class, $result);
    }

    /**
     * 测试getFilterService方法
     */
    public function testGetFilterService(): void
    {
        // 创建具体的控制器实现
        $controller = $this->createTestController();

        // 创建模拟的服务
        $filterService = $this->createMock(FilterService::class);

        // 创建模拟的Container
        $container = $this->createMock(ContainerInterface::class);

        // 配置Container返回FilterService服务
        $container->method('get')
            ->with(FilterService::class)
            ->willReturn($filterService)
        ;

        // 设置container属性
        $reflection = new \ReflectionClass($controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($controller, $container);

        // 调用公开的测试方法
        /** @phpstan-ignore-next-line */
        $result = $controller->publicGetFilterService();

        // 验证结果是FilterService的实例
        $this->assertInstanceOf(FilterService::class, $result);
    }

    /**
     * 测试getFieldService方法
     */
    public function testGetFieldService(): void
    {
        // 创建具体的控制器实现
        $controller = $this->createTestController();

        // 创建模拟的服务
        $fieldService = $this->createMock(FieldService::class);

        // 创建模拟的Container
        $container = $this->createMock(ContainerInterface::class);

        // 配置Container返回FieldService服务
        $container->method('get')
            ->with(FieldService::class)
            ->willReturn($fieldService)
        ;

        // 设置container属性
        $reflection = new \ReflectionClass($controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($controller, $container);

        // 调用公开的测试方法
        /** @phpstan-ignore-next-line */
        $result = $controller->publicGetFieldService();

        // 验证结果是FieldService的实例
        $this->assertInstanceOf(FieldService::class, $result);
    }
}
