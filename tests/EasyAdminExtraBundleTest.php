<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests;

use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\EasyAdminExtraBundle\EasyAdminExtraBundle;
use Tourze\EcolBundle\EcolBundle;

/**
 * @internal
 *
 * @phpstan-ignore-next-line 测试Bundle的基本功能，不需要数据库集成
 */
#[CoversClass(EasyAdminExtraBundle::class)]
final class EasyAdminExtraBundleTest extends TestCase
{
    public function testBundleExtendsCorrectBaseClass(): void
    {
        $bundle = new EasyAdminExtraBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
    }

    public function testBundleImplementsBundleDependencyInterface(): void
    {
        $bundle = new EasyAdminExtraBundle();
        $this->assertInstanceOf(BundleDependencyInterface::class, $bundle);
    }

    public function testGetBundleDependenciesReturnsExpectedDependencies(): void
    {
        $expectedDependencies = [
            TwigBundle::class => ['all' => true],
            EasyAdminBundle::class => ['all' => true],
            EcolBundle::class => ['all' => true],
        ];

        $actualDependencies = EasyAdminExtraBundle::getBundleDependencies();

        $this->assertSame($expectedDependencies, $actualDependencies);
    }

    public function testGetBundleDependenciesContainsRequiredBundles(): void
    {
        $dependencies = EasyAdminExtraBundle::getBundleDependencies();

        $this->assertArrayHasKey(TwigBundle::class, $dependencies);
        $this->assertArrayHasKey(EasyAdminBundle::class, $dependencies);
        $this->assertArrayHasKey(EcolBundle::class, $dependencies);
    }

    public function testEachDependencyIsEnabledForAllEnvironments(): void
    {
        $dependencies = EasyAdminExtraBundle::getBundleDependencies();

        foreach ($dependencies as $dependency) {
            $this->assertSame(['all' => true], $dependency);
        }
    }
}
