<?php

namespace Tourze\EasyAdminExtraBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\EasyAdminExtraBundle\DependencyInjection\EasyAdminExtraExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(EasyAdminExtraExtension::class)]
final class EasyAdminExtraExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    /**
     * 重写排除抽象类的服务目录提供器
     */
    protected function provideServiceDirectories(): iterable
    {
        yield 'Service';
        yield 'Repository';
        yield 'EventSubscriber';
        yield 'MessageHandler';
        yield 'Procedure';
    }
}
