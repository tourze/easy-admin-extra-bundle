<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\TextHelper;

/**
 * @internal
 * @phpstan-ignore-next-line serviceTestShouldNotInheritTestCase.inheritance
 */
#[CoversClass(TextHelper::class)]
final class TextHelperTest extends TestCase
{
    private TextHelper $textHelper;

    private DocBlockFactoryInterface $docBlockFactory;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->docBlockFactory = $this->createMock(DocBlockFactoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->textHelper = new TextHelper($this->docBlockFactory, $this->entityManager);
    }

    public function testGetTitleFromReflectionReturnsEmptyForClassWithoutDocComment(): void
    {
        $reflection = new \ReflectionClass(\stdClass::class);

        $result = $this->textHelper->getTitleFromReflection($reflection);

        $this->assertSame('', $result);
    }

    public function testGetTitleFromReflectionExtractsChineseTitle(): void
    {
        // 模拟一个带有文档注释的反射类
        $reflection = $this->createMock(\ReflectionClass::class);
        $reflection->method('getName')->willReturn('TestClass');
        $reflection->method('getAttributes')->willReturn([]);
        $reflection->method('getDocComment')->willReturn('/**\n * 测试标题\n */');

        // 创建真实的 DocBlockFactory
        $docBlockFactory = DocBlockFactory::createInstance();
        $textHelper = new TextHelper($docBlockFactory, $this->entityManager);

        $result = $textHelper->getTitleFromReflection($reflection);

        // DocBlock parser 保留了格式，所以需要匹配实际输出
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('测试标题', $result);
    }

    public function testGetTitleFromReflectionHandlesMultilineComment(): void
    {
        // 模拟一个带有多行注释的反射类
        $reflection = $this->createMock(\ReflectionClass::class);
        $reflection->method('getName')->willReturn('TestClass');
        $reflection->method('getAttributes')->willReturn([]);
        $reflection->method('getDocComment')->willReturn('/**\n * 这是一个多行注释\n * 包含详细说明\n */');

        // 创建真实的 DocBlockFactory
        $docBlockFactory = DocBlockFactory::createInstance();
        $textHelper = new TextHelper($docBlockFactory, $this->entityManager);

        $result = $textHelper->getTitleFromReflection($reflection);

        // DocBlock parser 会去除前导的 * 和空格，但保留换行符
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('这是一个多行注释', $result);
    }
}
