# EasyAdminExtraBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/easy-admin-extra-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-extra-bundle)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Extension bundle for EasyAdmin, providing utility features for working with Symfony EasyAdmin.

## Features

- **EntityDescriber**: Read entity information, get property labels, column names, and field length.
- **RepositoryTreeDataFetcher**: Fetch and manipulate tree structure data from a repository.
- **ChoiceService**: Create options from enum types for form fields.
- **ColumnService**: Handle column-related functionality, especially for enum type columns.
- **ImportService**: Process import functionality, identify importable properties.

## Requirements

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 2.17 or 3.0+

## Installation

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
