<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\EasyAdminExtraBundle\Exception\BusinessRuleException;
use Tourze\EasyAdminExtraBundle\Service\RepositoryTreeDataFetcher;

/**
 * @internal
 */
#[CoversClass(RepositoryTreeDataFetcher::class)]
final class RepositoryTreeDataFetcherTest extends TestCase
{
    private RepositoryTreeDataFetcher $fetcher;

    private ManagerRegistry $registry;

    private NormalizerInterface $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建依赖项的模拟
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);

        $this->fetcher = new RepositoryTreeDataFetcher($this->registry, $this->normalizer);
    }

    /**
     * 测试基本的getter和setter方法
     */
    public function testEntityClassGetterSetter(): void
    {
        // 初始状态应该为null
        $this->assertNull($this->fetcher->getEntityClass());

        // 设置实体类
        $this->fetcher->setEntityClass('TestEntity');
        $this->assertEquals('TestEntity', $this->fetcher->getEntityClass());

        // 设置为null
        $this->fetcher->setEntityClass(null);
        $this->assertNull($this->fetcher->getEntityClass());
    }

    /**
     * 测试当未设置实体类时抛出异常
     */
    public function testGenTreeDataThrowsExceptionWhenEntityClassNotSet(): void
    {
        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Entity class is not set');

        $this->fetcher->genTreeData();
    }

    /**
     * 测试当找不到管理器时抛出异常
     */
    public function testGenTreeDataThrowsExceptionWhenManagerNotFound(): void
    {
        $this->fetcher->setEntityClass('NonExistentEntity');

        $this->registry
            ->method('getManagerForClass')
            ->with('NonExistentEntity')
            ->willReturn(null)
        ;

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Manager not found for entity class: NonExistentEntity');

        $this->fetcher->genTreeData();
    }

    /**
     * 测试成功生成树形数据
     */
    public function testGenTreeDataSuccessful(): void
    {
        $this->fetcher->setEntityClass('TestEntity');

        // 模拟实体对象
        $entity1 = new \stdClass();
        $entity1->id = 1;
        $entity1->name = 'Root';

        $entity2 = new \stdClass();
        $entity2->id = 2;
        $entity2->name = 'Child';

        // 创建模拟的仓库
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->method('findBy')
            ->with(['parent' => null])
            ->willReturn([$entity1, $entity2])
        ;

        // 创建模拟的管理器
        $manager = $this->createMock(ObjectManager::class);
        $manager
            ->method('getRepository')
            ->with('TestEntity')
            ->willReturn($repository)
        ;

        $this->registry
            ->method('getManagerForClass')
            ->with('TestEntity')
            ->willReturn($manager)
        ;

        // 模拟序列化器的行为
        $this->normalizer
            ->method('normalize')
            ->willReturnCallback(function ($entity) {
                if (1 === $entity->id) {
                    return ['id' => 1, 'name' => 'Root'];
                }

                return ['id' => 2, 'name' => 'Child'];
            })
        ;

        $result = $this->fetcher->genTreeData();

        // 验证结果包含了键值映射
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // 验证第一项的键值映射
        $this->assertArrayHasKey('key', $result[0]);
        $this->assertArrayHasKey('value', $result[0]);
        $this->assertArrayHasKey('title', $result[0]);
        $this->assertEquals(1, $result[0]['key']);
        $this->assertEquals(1, $result[0]['value']);
        $this->assertEquals('Root', $result[0]['title']);
    }

    /**
     * 测试检查子元素是否为空
     */
    public function testCheckIfChildrenEmpty(): void
    {
        // 没有children键的情况
        $item1 = ['id' => 1, 'name' => 'test'];
        $this->assertTrue($this->fetcher->checkIfChildrenEmpty($item1));

        // children为空数组的情况
        $item2 = ['id' => 2, 'name' => 'test', 'children' => []];
        $this->assertTrue($this->fetcher->checkIfChildrenEmpty($item2));

        // children不为空的情况
        $item3 = ['id' => 3, 'name' => 'test', 'children' => [['id' => 4]]];
        $this->assertFalse($this->fetcher->checkIfChildrenEmpty($item3));
    }

    /**
     * 测试检查树形结构
     */
    public function testCheckTree(): void
    {
        // 第一层级（level=1），有空子元素的项目不应该被删除
        $tree = [
            ['id' => 1, 'children' => []],
            ['id' => 2, 'children' => [['id' => 3]]],
        ];

        $result = $this->fetcher->checkTree($tree, 1);
        $this->assertCount(2, $result);

        // 第二层级及以上，空子元素的项目应该被删除
        $tree2 = [
            ['id' => 1, 'children' => []],
            ['id' => 2, 'children' => [['id' => 3]]],
        ];

        $result2 = $this->fetcher->checkTree($tree2, 2);
        $this->assertCount(1, $result2);
        $this->assertEquals(2, $result2[0]['id']);
    }

    /**
     * 测试递归处理子元素
     */
    public function testCheckTreeRecursive(): void
    {
        $tree = [
            [
                'id' => 1,
                'children' => [
                    ['id' => 2, 'children' => []],  // 这个会被删除
                    ['id' => 3, 'children' => [['id' => 4]]],  // 这个保留
                ],
            ],
        ];

        $result = $this->fetcher->checkTree($tree, 1);

        // 第一层保留，但其子元素中空children的被删除
        $this->assertCount(1, $result);
        $this->assertCount(1, $result[0]['children']);
        $this->assertEquals(3, $result[0]['children'][0]['id']);
    }

    /**
     * 测试追加键值映射
     */
    public function testAppendKeyValue(): void
    {
        $tree = [
            [
                'id' => 1,
                'name' => 'Root',
                'children' => [
                    ['id' => 2, 'name' => 'Child'],
                ],
            ],
        ];

        $result = $this->fetcher->appendKeyValue($tree);

        // 验证根级别的键值映射
        $this->assertArrayHasKey('key', $result[0]);
        $this->assertArrayHasKey('value', $result[0]);
        $this->assertArrayHasKey('title', $result[0]);
        $this->assertEquals(1, $result[0]['key']);
        $this->assertEquals(1, $result[0]['value']);
        $this->assertEquals('Root', $result[0]['title']);

        // 验证子级别的键值映射
        $this->assertArrayHasKey('key', $result[0]['children'][0]);
        $this->assertArrayHasKey('value', $result[0]['children'][0]);
        $this->assertArrayHasKey('title', $result[0]['children'][0]);
        $this->assertEquals(2, $result[0]['children'][0]['key']);
        $this->assertEquals(2, $result[0]['children'][0]['value']);
        $this->assertEquals('Child', $result[0]['children'][0]['title']);
    }

    /**
     * 测试空树的处理
     */
    public function testAppendKeyValueWithEmptyTree(): void
    {
        $result = $this->fetcher->appendKeyValue([]);
        $this->assertEquals([], $result);
    }

    /**
     * 测试没有子元素的项目
     */
    public function testAppendKeyValueWithoutChildren(): void
    {
        $tree = [
            ['id' => 1, 'name' => 'Single Item'],
        ];

        $result = $this->fetcher->appendKeyValue($tree);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['key']);
        $this->assertEquals(1, $result[0]['value']);
        $this->assertEquals('Single Item', $result[0]['title']);
    }
}
