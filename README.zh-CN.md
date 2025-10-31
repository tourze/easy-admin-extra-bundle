# EasyAdminExtraBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/easy-admin-extra-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-extra-bundle)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-extra-bundle)

[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

EasyAdmin的扩展功能包，提供基于注解的自动化配置和增强功能。

## 功能

- **AbstractCrudController**: 增强的CRUD控制器，支持自动字段配置、权限控制和实体复制功能
- **注解驱动配置**: 使用PHP 8属性实现自动字段和过滤器配置
- **字段服务**: 基于Doctrine映射和注解的自动字段类型检测和配置
- **过滤器系统**: 基于`@Filterable`注解的自动过滤器创建
- **搜索系统**: 使用`@Keyword`注解的智能搜索字段提取
- **实体复制**: 内置实体克隆支持，可配置复制规则
- **事件系统**: 创建和修改操作的前置/后置事件
- **树形数据支持**: 获取和操作层次结构数据
- **导入导出支持**: 识别和处理可导入的实体属性
- **多语言支持**: 标签和消息的国际化支持

## 安装

### 环境要求

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 2.17 或 3.0+

### 通过Composer安装

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

### AbstractCrudController

主要功能是增强的CRUD控制器，它基于注解自动配置字段和过滤器：

```php
use Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
}
```

### 注解驱动的实体配置

使用PHP 8属性来配置您的实体：

```php
use Tourze\EasyAdminAttribute\Attribute\FormField;
use Tourze\EasyAdminAttribute\Attribute\ListColumn;
use Tourze\EasyAdminAttribute\Attribute\Filterable;
use Tourze\EasyAdminAttribute\Attribute\Keyword;

class User
{
    #[ListColumn(order: 1)]
    #[FormField(order: 1)]
    #[Filterable]
    #[Keyword]
    private string $username;

    #[ListColumn(order: 2)]
    #[FormField(order: 2)]
    #[Filterable]
    private string $email;

    // 其他属性...
}
```

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

### RepositoryTreeDataFetcher

从仓库获取和处理树形结构数据：

```php
use Tourze\EasyAdminExtraBundle\Service\RepositoryTreeDataFetcher;

class MyTreeService
{
    public function __construct(
        private readonly RepositoryTreeDataFetcher $treeFetcher,
    ) {
    }

    public function getTreeData(string $entityClass): array
    {
        $this->treeFetcher->setEntityClass($entityClass);
        return $this->treeFetcher->genTreeData();
    }
}
```

## 高级用法

### 自定义字段创建

你可以扩展FieldService来创建自定义字段类型或修改现有字段行为：

```php
use Tourze\EasyAdminExtraBundle\Service\FieldService;

class CustomFieldService extends FieldService
{
    public function createCustomField(\ReflectionProperty $property): ?FieldInterface
    {
        // 自定义字段创建逻辑
        return null;
    }
}
```

### 树形数据操作

对于复杂的树形数据操作，你可以扩展RepositoryTreeDataFetcher：

```php
use Tourze\EasyAdminExtraBundle\Service\RepositoryTreeDataFetcher;

class CustomTreeDataFetcher extends RepositoryTreeDataFetcher
{
    public function customTreeProcessing(array $tree): array
    {
        // 自定义树形处理逻辑
        return $tree;
    }
}
```

### 实体复制操作

使用CopyableRepository trait实现实体复制功能：

```php
use Tourze\EasyAdminExtraBundle\Contract\CopyableRepository;

class MyEntityRepository extends ServiceEntityRepository
{
    use CopyableRepository;
    
    // 仓库实现
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

## 贡献

欢迎贡献！请随时提交Pull Request。

## License

此包采用MIT许可证。详情请参阅[LICENSE](LICENSE)文件。
