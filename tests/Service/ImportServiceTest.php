<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\ImportService;

/**
 * @internal
 */
#[CoversClass(ImportService::class)]
final class ImportServiceTest extends TestCase
{
    private ImportService $importService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importService = new ImportService();
    }

    /**
     * 测试获取可能的导入属性
     */
    public function testGetPossibleProperties(): void
    {
        $reflectionClass = new \ReflectionClass(TestImportEntity::class);

        $properties = $this->importService->getPossibleProperties($reflectionClass);
        $propertiesArray = iterator_to_array($properties);

        // 应该只返回主键ID和唯一索引的code属性
        $this->assertCount(2, $propertiesArray);

        $propertyNames = array_map(function (\ReflectionProperty $prop): string {
            return $prop->getName();
        }, $propertiesArray);

        $this->assertContains('id', $propertyNames);
        $this->assertContains('code', $propertyNames);
        $this->assertNotContains('name', $propertyNames);
        $this->assertNotContains('nonColumnProperty', $propertyNames);
    }
}
