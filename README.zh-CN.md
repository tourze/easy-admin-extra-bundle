# EasyAdminExtraBundle

EasyAdmin的扩展功能包，提供了一些常用的辅助功能。

## 功能

- **EntityDescriber**: 读取实体信息，获取属性标签、列名和长度等。
- **RepositoryTreeDataFetcher**: 从仓库获取树形结构数据。
- **ChoiceService**: 从枚举类型创建选项。
- **ColumnService**: 处理与列相关的功能，特别是枚举类型的列。
- **ImportService**: 处理导入功能，识别可导入的属性。

## 安装

使用Composer安装:

```bash
composer require tourze/easy-admin-extra-bundle
```

## 配置

在`bundles.php`中添加:

```php
Tourze\EasyAdminExtraBundle\EasyAdminExtraBundle::class => ['all' => true],
```

## 使用

### EntityDescriber

```php
use Tourze\EasyAdminExtraBundle\Service\EntityDescriber;

class MyService
{
    public function __construct(
        private readonly EntityDescriber $entityDescriber,
    ) {
    }

    public function example(\ReflectionProperty $property): string
    {
        // 获取属性标签
        return $this->entityDescriber->getPropertyLabel($property);
    }
}
```

### ChoiceService

```php
use Tourze\EasyAdminExtraBundle\Service\ChoiceService;

class MyService
{
    public function __construct(
        private readonly ChoiceService $choiceService,
    ) {
    }

    public function example(string $enumClass): array
    {
        // 从枚举创建选项
        return iterator_to_array($this->choiceService->createChoicesFromEnum($enumClass));
    }
}
```

## 测试

运行测试:

```bash
cd packages/easy-admin-extra-bundle
composer install
php vendor/bin/phpunit
```

或者在monorepo根目录使用:

```bash
php vendor/bin/phpunit packages/easy-admin-extra-bundle
```
