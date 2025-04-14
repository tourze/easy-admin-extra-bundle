<?php

namespace Tourze\EasyAdminExtraBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\EasyAdminExtraBundle\DependencyInjection\EasyAdminExtraExtension;
use Tourze\EasyAdminExtraBundle\Service\ChoiceService;
use Tourze\EasyAdminExtraBundle\Service\ColumnService;
use Tourze\EasyAdminExtraBundle\Service\EntityDescriber;
use Tourze\EasyAdminExtraBundle\Service\ImportService;
use Tourze\EasyAdminExtraBundle\Service\RepositoryTreeDataFetcher;

class EasyAdminExtraExtensionTest extends TestCase
{
    private ContainerBuilder $container;
    private EasyAdminExtraExtension $extension;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new EasyAdminExtraExtension();
    }

    /**
     * 测试加载扩展
     */
    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        // 验证服务是否已注册
        $this->assertTrue($this->container->hasDefinition(ChoiceService::class));
        $this->assertTrue($this->container->hasDefinition(ColumnService::class));
        $this->assertTrue($this->container->hasDefinition(EntityDescriber::class));
        $this->assertTrue($this->container->hasDefinition(ImportService::class));
        $this->assertTrue($this->container->hasDefinition(RepositoryTreeDataFetcher::class));
    }
}
