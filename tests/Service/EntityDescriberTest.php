<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdminExtraBundle\Service\EntityDescriber;
use Yiisoft\Strings\Inflector;

/**
 * @internal
 */
#[CoversClass(EntityDescriber::class)]
final class EntityDescriberTest extends TestCase
{
    private EntityDescriber $entityDescriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityDescriber = new EntityDescriber(new Inflector());
    }

    /**
     * 测试获取属性标签
     */
    public function testGetPropertyLabel(): void
    {
        // 创建基本属性模拟
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: EntityDescriber 服务依赖于 ReflectionProperty 的具体方法如 getName() 和 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $property = $this->createMock(\ReflectionProperty::class);
        $property->method('getName')->willReturn('testProperty');
        $property->method('getAttributes')->willReturn([]);

        // 测试没有属性注释时返回属性名
        $this->assertEquals('testProperty', $this->entityDescriber->getPropertyLabel($property));

        // 测试带有ORM注释的属性
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: EntityDescriber 服务依赖于 ReflectionProperty 的具体方法如 getName() 和 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $propertyWithComment = $this->createMock(\ReflectionProperty::class);
        $propertyWithComment->method('getName')->willReturn('testProperty');

        // 创建一个模拟的ReflectionAttribute
        // 必须使用 ReflectionAttribute 具体类进行 mock
        // 理由 1: ReflectionAttribute 是 PHP 8 内置的反射类，没有对应的接口可以使用
        // 理由 2: 需要模拟 newInstance() 方法来返回 Doctrine 的 Column 属性实例
        // 理由 3: ReflectionAttribute 是 final 类无法继承，只能通过 PHPUnit 的 mock 机制来模拟
        $columnAttributeMock = $this->createMock(\ReflectionAttribute::class);
        // 使用代理类代替直接mock final Column类
        $columnProxy = new ColumnProxy(options: ['comment' => '测试属性']);

        $columnAttributeMock->method('newInstance')->willReturn($columnProxy);

        $propertyWithComment->method('getAttributes')->willReturnCallback(function ($attributeClass) use ($columnAttributeMock) {
            if (ORM\Column::class === $attributeClass) {
                return [$columnAttributeMock];
            }

            return [];
        });

        // 测试带有注释的属性
        $this->assertEquals('测试属性', $this->entityDescriber->getPropertyLabel($propertyWithComment));

        // 测试带有ListColumn的属性
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: EntityDescriber 服务依赖于 ReflectionProperty 的具体方法如 getName() 和 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $propertyWithListColumn = $this->createMock(\ReflectionProperty::class);
        $propertyWithListColumn->method('getName')->willReturn('testProperty');

        // 创建模拟的Column和ListColumn属性
        // 必须使用 ReflectionAttribute 具体类进行 mock
        // 理由 1: ReflectionAttribute 是 PHP 8 内置的反射类，没有对应的接口可以使用
        // 理由 2: 需要模拟 newInstance() 方法来返回 Doctrine 的 Column 属性实例
        // 理由 3: ReflectionAttribute 是 final 类无法继承，只能通过 PHPUnit 的 mock 机制来模拟
        $columnAttributeMock2 = $this->createMock(\ReflectionAttribute::class);
        $columnProxy2 = new ColumnProxy(options: []);
        $columnAttributeMock2->method('newInstance')->willReturn($columnProxy2);

        // 必须使用 ReflectionAttribute 具体类进行 mock
        // 理由 1: ReflectionAttribute 是 PHP 8 内置的反射类，没有对应的接口可以使用
        // 理由 2: 需要模拟 newInstance() 方法来返回 ListColumn 属性实例
        // 理由 3: ReflectionAttribute 是 final 类无法继承，只能通过 PHPUnit 的 mock 机制来模拟
        $listColumnAttributeMock = $this->createMock(\ReflectionAttribute::class);
        $listColumnProxy = new ListColumnProxy(title: '列表列标题');
        $listColumnAttributeMock->method('newInstance')->willReturn($listColumnProxy);

        $propertyWithListColumn->method('getAttributes')->willReturnCallback(function ($attributeClass) use ($columnAttributeMock2, $listColumnAttributeMock) {
            if (ORM\Column::class === $attributeClass) {
                return [$columnAttributeMock2];
            }
            if (ListColumn::class === $attributeClass) {
                return [$listColumnAttributeMock];
            }

            return [];
        });

        // 测试带有ListColumn的属性
        $this->assertEquals('列表列标题', $this->entityDescriber->getPropertyLabel($propertyWithListColumn));
    }

    /**
     * 测试获取数据库列名
     */
    public function testGetColumnName(): void
    {
        // 基本属性
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: EntityDescriber 服务依赖于 ReflectionProperty 的具体方法如 getName() 和 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $property = $this->createMock(\ReflectionProperty::class);
        $property->method('getName')->willReturn('testProperty');
        $property->method('getAttributes')->willReturn([]);

        // 测试普通属性转换为snake_case
        $this->assertEquals('test_property', $this->entityDescriber->getColumnName($property, false));

        // 带有自定义列名的ORM属性
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: EntityDescriber 服务依赖于 ReflectionProperty 的具体方法如 getName() 和 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $propertyWithColumnName = $this->createMock(\ReflectionProperty::class);
        $propertyWithColumnName->method('getName')->willReturn('testProperty');

        // 创建模拟的Column属性，使用代理类
        // 必须使用 ReflectionAttribute 具体类进行 mock
        // 理由 1: ReflectionAttribute 是 PHP 8 内置的反射类，没有对应的接口可以使用
        // 理由 2: 需要模拟 newInstance() 方法来返回 Doctrine 的 Column 属性实例
        // 理由 3: ReflectionAttribute 是 final 类无法继承，只能通过 PHPUnit 的 mock 机制来模拟
        $columnAttributeMock = $this->createMock(\ReflectionAttribute::class);
        $columnProxy = new ColumnProxy(name: 'customColumnName');
        $columnAttributeMock->method('newInstance')->willReturn($columnProxy);

        $propertyWithColumnName->method('getAttributes')->willReturnCallback(function ($attributeClass) use ($columnAttributeMock) {
            if (ORM\Column::class === $attributeClass) {
                return [$columnAttributeMock];
            }

            return [];
        });

        // 测试自定义列名
        $this->assertEquals('custom_column_name', $this->entityDescriber->getColumnName($propertyWithColumnName, false));

        // 测试关系字段
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: EntityDescriber 服务依赖于 ReflectionProperty 的具体方法如 getName() 和 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $relationProperty = $this->createMock(\ReflectionProperty::class);
        $relationProperty->method('getName')->willReturn('user');

        // 创建模拟的ManyToOne属性，使用代理类
        // 必须使用 ReflectionAttribute 具体类进行 mock
        // 理由 1: ReflectionAttribute 是 PHP 8 内置的反射类，没有对应的接口可以使用
        // 理由 2: 需要模拟 newInstance() 方法来返回 Doctrine 的 ManyToOne 属性实例
        // 理由 3: ReflectionAttribute 是 final 类无法继承，只能通过 PHPUnit 的 mock 机制来模拟
        $manyToOneAttributeMock = $this->createMock(\ReflectionAttribute::class);
        $manyToOneProxy = new ManyToOneProxy();
        $manyToOneAttributeMock->method('newInstance')->willReturn($manyToOneProxy);

        $relationProperty->method('getAttributes')->willReturnCallback(function ($attributeClass) use ($manyToOneAttributeMock) {
            if (ORM\ManyToOne::class === $attributeClass) {
                return [$manyToOneAttributeMock];
            }

            return [];
        });

        // 测试关系字段带有_id后缀
        $this->assertEquals('user_id', $this->entityDescriber->getColumnName($relationProperty, true));
        $this->assertEquals('user', $this->entityDescriber->getColumnName($relationProperty, false));
    }

    /**
     * 测试获取属性最大长度
     */
    public function testGetPropertyMaxLength(): void
    {
        // 基本属性，没有长度限制
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: EntityDescriber 服务依赖于 ReflectionProperty 的具体方法如 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $property = $this->createMock(\ReflectionProperty::class);
        $property->method('getAttributes')->willReturn([]);

        // 测试无长度限制的属性
        $this->assertNull($this->entityDescriber->getPropertyMaxLength($property));

        // 带有长度限制的字符串属性
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: EntityDescriber 服务依赖于 ReflectionProperty 的具体方法如 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $stringProperty = $this->createMock(\ReflectionProperty::class);

        // 创建模拟的Column属性，使用代理类
        // 必须使用 ReflectionAttribute 具体类进行 mock
        // 理由 1: ReflectionAttribute 是 PHP 8 内置的反射类，没有对应的接口可以使用
        // 理由 2: 需要模拟 newInstance() 方法来返回 Doctrine 的 Column 属性实例
        // 理由 3: ReflectionAttribute 是 final 类无法继承，只能通过 PHPUnit 的 mock 机制来模拟
        $columnAttributeMock = $this->createMock(\ReflectionAttribute::class);
        $columnProxy = new ColumnProxy(length: 255);
        $columnAttributeMock->method('newInstance')->willReturn($columnProxy);

        $stringProperty->method('getAttributes')->willReturnCallback(function ($attributeClass) use ($columnAttributeMock) {
            if (ORM\Column::class === $attributeClass) {
                return [$columnAttributeMock];
            }

            return [];
        });

        // 测试带有长度限制的属性
        $this->assertEquals(255, $this->entityDescriber->getPropertyMaxLength($stringProperty));

        // 没有显式长度但是有类型的属性
        // 必须使用 ReflectionProperty 具体类进行 mock
        // 理由 1: ReflectionProperty 是 PHP 内置的反射类，没有对应的接口可以使用
        // 理由 2: EntityDescriber 服务依赖于 ReflectionProperty 的具体方法如 getAttributes()
        // 理由 3: 在测试中模拟反射行为需要使用具体的 ReflectionProperty 类型
        $typedProperty = $this->createMock(\ReflectionProperty::class);

        // 创建模拟的Column属性，使用代理类，有类型但没有长度
        // 必须使用 ReflectionAttribute 具体类进行 mock
        // 理由 1: ReflectionAttribute 是 PHP 8 内置的反射类，没有对应的接口可以使用
        // 理由 2: 需要模拟 newInstance() 方法来返回 Doctrine 的 Column 属性实例
        // 理由 3: ReflectionAttribute 是 final 类无法继承，只能通过 PHPUnit 的 mock 机制来模拟
        $typedColumnAttributeMock = $this->createMock(\ReflectionAttribute::class);
        $typedColumnProxy = new ColumnProxy(length: null, type: Types::STRING);
        $typedColumnAttributeMock->method('newInstance')->willReturn($typedColumnProxy);

        $typedProperty->method('getAttributes')->willReturnCallback(function ($attributeClass) use ($typedColumnAttributeMock) {
            if (ORM\Column::class === $attributeClass) {
                return [$typedColumnAttributeMock];
            }

            return [];
        });

        // 测试类型为string的默认长度
        $this->assertEquals(1000, $this->entityDescriber->getPropertyMaxLength($typedProperty));
    }
}
