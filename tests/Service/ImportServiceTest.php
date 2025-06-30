<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\ImportService;

/**
 * 测试实体类
 * @internal
 */
class TestImportEntity
{
    #[ORM\Id]
    #[ORM\Column]
    public int $id;

    #[ORM\Column]
    public string $name;

    #[ORM\Column(unique: true)]
    public string $code;

    public string $nonColumnProperty;
}

class ImportServiceTest extends TestCase
{
    private ImportService $importService;

    protected function setUp(): void
    {
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

        $propertyNames = array_map(function (\ReflectionProperty $prop) {
            return $prop->getName();
        }, $propertiesArray);

        $this->assertContains('id', $propertyNames);
        $this->assertContains('code', $propertyNames);
        $this->assertNotContains('name', $propertyNames);
        $this->assertNotContains('nonColumnProperty', $propertyNames);
    }
}
