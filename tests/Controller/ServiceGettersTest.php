<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController;
use Tourze\EasyAdminExtraBundle\Service\FieldService;
use Tourze\EasyAdminExtraBundle\Service\FilterService;
use Tourze\EasyAdminExtraBundle\Service\TextHelper;

/**
 * 测试AbstractCrudController中的Service Getter方法
 */
class ServiceGettersTest extends TestCase
{
    /**
     * 测试getTextHelper方法
     */
    public function testGetTextHelper(): void
    {
        // 创建一个模拟的Controller
        $controller = $this->getMockForAbstractClass(
            AbstractCrudController::class
        );

        // 创建模拟的服务
        $textHelper = $this->createMock(TextHelper::class);

        // 创建模拟的Container
        $container = $this->createMock(ContainerInterface::class);

        // 配置Container返回TextHelper服务
        $container->method('get')
            ->with(TextHelper::class)
            ->willReturn($textHelper);

        // 设置container属性
        $reflection = new \ReflectionClass($controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($controller, $container);

        // 获取并测试getTextHelper方法
        $getTextHelperMethod = $reflection->getMethod('getTextHelper');
        $getTextHelperMethod->setAccessible(true);
        $result = $getTextHelperMethod->invoke($controller);

        // 验证结果是TextHelper的实例
        $this->assertInstanceOf(TextHelper::class, $result);
    }

    /**
     * 测试getLogger方法
     */
    public function testGetLogger(): void
    {
        // 创建一个模拟的Controller
        $controller = $this->getMockForAbstractClass(
            AbstractCrudController::class
        );

        // 创建模拟的服务
        $logger = $this->createMock(LoggerInterface::class);

        // 创建模拟的Container
        $container = $this->createMock(ContainerInterface::class);

        // 配置Container返回Logger服务
        $container->method('get')
            ->with(LoggerInterface::class)
            ->willReturn($logger);

        // 设置container属性
        $reflection = new \ReflectionClass($controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($controller, $container);

        // 获取并测试getLogger方法
        $getLoggerMethod = $reflection->getMethod('getLogger');
        $getLoggerMethod->setAccessible(true);
        $result = $getLoggerMethod->invoke($controller);

        // 验证结果是LoggerInterface的实例
        $this->assertInstanceOf(LoggerInterface::class, $result);
    }

    /**
     * 测试getFilterService方法
     */
    public function testGetFilterService(): void
    {
        // 创建一个模拟的Controller
        $controller = $this->getMockForAbstractClass(
            AbstractCrudController::class
        );

        // 创建模拟的服务
        $filterService = $this->createMock(FilterService::class);

        // 创建模拟的Container
        $container = $this->createMock(ContainerInterface::class);

        // 配置Container返回FilterService服务
        $container->method('get')
            ->with(FilterService::class)
            ->willReturn($filterService);

        // 设置container属性
        $reflection = new \ReflectionClass($controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($controller, $container);

        // 获取并测试getFilterService方法
        $getFilterServiceMethod = $reflection->getMethod('getFilterService');
        $getFilterServiceMethod->setAccessible(true);
        $result = $getFilterServiceMethod->invoke($controller);

        // 验证结果是FilterService的实例
        $this->assertInstanceOf(FilterService::class, $result);
    }

    /**
     * 测试getFieldService方法
     */
    public function testGetFieldService(): void
    {
        // 创建一个模拟的Controller
        $controller = $this->getMockForAbstractClass(
            AbstractCrudController::class
        );

        // 创建模拟的服务
        $fieldService = $this->createMock(FieldService::class);

        // 创建模拟的Container
        $container = $this->createMock(ContainerInterface::class);

        // 配置Container返回FieldService服务
        $container->method('get')
            ->with(FieldService::class)
            ->willReturn($fieldService);

        // 设置container属性
        $reflection = new \ReflectionClass($controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($controller, $container);

        // 获取并测试getFieldService方法
        $getFieldServiceMethod = $reflection->getMethod('getFieldService');
        $getFieldServiceMethod->setAccessible(true);
        $result = $getFieldServiceMethod->invoke($controller);

        // 验证结果是FieldService的实例
        $this->assertInstanceOf(FieldService::class, $result);
    }
}
