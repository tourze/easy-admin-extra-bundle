# EasyAdminExtraBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/easy-admin-extra-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-extra-bundle)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-extra-bundle)

[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Extension bundle for EasyAdmin, providing annotation-driven utilities and enhanced features for Symfony EasyAdmin.

## Features

- **AbstractCrudController**: Enhanced CRUD controller with automatic field configuration, permission control, and entity copying support
- **Annotation-Driven Configuration**: Uses PHP 8 attributes for automatic field and filter configuration
- **Field Services**: Automatic field type detection and configuration based on Doctrine mapping and annotations
- **Filter System**: Automatic filter creation based on `@Filterable` annotations
- **Search System**: Smart search field extraction using `@Keyword` annotations
- **Entity Copying**: Built-in support for entity cloning with configurable copy rules
- **Event System**: Pre/post operation events for create and modify operations
- **Tree Data Support**: Fetch and manipulate hierarchical data structures
- **Import/Export Support**: Identify and process importable entity properties
- **Multi-language Support**: Internationalization support for labels and messages

## Installation

### Requirements

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 2.17 or 3.0+

### Install via Composer

Install the package via Composer:

```bash
composer require tourze/easy-admin-extra-bundle
```

## Configuration

Add the bundle to your `config/bundles.php`:

```php
Tourze\EasyAdminExtraBundle\EasyAdminExtraBundle::class => ['all' => true],
```

## Usage

### AbstractCrudController

The main feature is the enhanced CRUD controller that automatically configures fields and filters based on annotations:

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

### Annotation-Driven Entity Configuration

Use PHP 8 attributes to configure your entities:

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

    // Other properties...
}
```

### EntityDescriber

This service reads entity information and provides methods to get property labels, column names, and field length.

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
        // Get property label
        return $this->entityDescriber->getPropertyLabel($property);
    }
}
```

### ChoiceService

Create options from enum types for use in forms.

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
        // Create choices from enum
        return iterator_to_array($this->choiceService->createChoicesFromEnum($enumClass));
    }
}
```

### RepositoryTreeDataFetcher

Fetch and process tree-structured data from repositories.

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

## Advanced Usage

### Custom Field Creation

You can extend the FieldService to create custom field types or modify existing field behavior:

```php
use Tourze\EasyAdminExtraBundle\Service\FieldService;

class CustomFieldService extends FieldService
{
    public function createCustomField(\ReflectionProperty $property): ?FieldInterface
    {
        // Custom field creation logic
        return null;
    }
}
```

### Tree Data Manipulation

For complex tree data operations, you can extend the RepositoryTreeDataFetcher:

```php
use Tourze\EasyAdminExtraBundle\Service\RepositoryTreeDataFetcher;

class CustomTreeDataFetcher extends RepositoryTreeDataFetcher
{
    public function customTreeProcessing(array $tree): array
    {
        // Custom tree processing logic
        return $tree;
    }
}
```

### Entity Copy Operations

Use the CopyableRepository trait for entity copying functionality:

```php
use Tourze\EasyAdminExtraBundle\Contract\CopyableRepository;

class MyEntityRepository extends ServiceEntityRepository
{
    use CopyableRepository;
    
    // Repository implementation
}
```

## Testing

Run tests using PHPUnit:

```bash
cd packages/easy-admin-extra-bundle
composer install
php vendor/bin/phpunit
```

Or from the monorepo root:

```bash
php vendor/bin/phpunit packages/easy-admin-extra-bundle
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This bundle is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
