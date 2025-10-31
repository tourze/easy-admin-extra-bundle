<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Service;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\EasyAdminExtraBundle\Exception\BusinessRuleException;
use Tourze\EnumExtra\TreeDataFetcher;

class RepositoryTreeDataFetcher implements TreeDataFetcher
{
    private ?string $entityClass = null;

    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    public function setEntityClass(?string $entityClass): void
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function genTreeData(): array
    {
        $entityClass = $this->getEntityClass();
        if (null === $entityClass) {
            throw new BusinessRuleException('Entity class is not set');
        }

        /** @var class-string<object> $entityClass */
        $manager = $this->registry->getManagerForClass($entityClass);
        if (null === $manager) {
            throw new BusinessRuleException('Manager not found for entity class: ' . $entityClass);
        }

        /** @var class-string<object> $entityClass */
        $_repo = $manager->getRepository($entityClass);

        $tree = [];
        // 先查找第一层
        foreach ($_repo->findBy(['parent' => null]) as $item) {
            $tree[] = $this->normalizer->normalize($item, 'array', [
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
                'groups' => 'api_tree',
            ]);
        }

        /** @var array<int, array<string, mixed>> $normalizedTree */
        $normalizedTree = $tree;

        return $this->appendKeyValue($normalizedTree);
    }

    /**
     * @param array<string, mixed> $item
     */
    public function checkIfChildrenEmpty(array $item): bool
    {
        if (!isset($item['children'])) {
            return true;
        }

        if (!is_array($item['children']) || [] === $item['children']) {
            return true;
        }

        return false;
    }

    /**
     * @param array<int, array<string, mixed>> $tree
     * @return array<int, array<string, mixed>>
     */
    public function checkTree(array $tree, int $level): array
    {
        $processedTree = [];

        foreach ($tree as $k => $item) {
            if ($this->checkIfChildrenEmpty($item) && 1 !== $level) {
                continue;
            }

            if (isset($item['children'])) {
                $item['children'] = $this->checkTree($item['children'], $level + 1);
            }

            $processedTree[] = $item;
        }

        return $processedTree;
    }

    /**
     * @param array<int, array<string, mixed>> $tree
     * @return array<int, array<string, mixed>>
     */
    public function appendKeyValue(array $tree): array
    {
        $keyValueMap = $this->getKeyValueMap();
        $processedTree = [];

        foreach ($tree as $item) {
            $processedItem = $this->mapItemKeys($item, $keyValueMap);
            $processedItem = $this->processChildrenRecursively($processedItem);
            $processedTree[] = $processedItem;
        }

        return $processedTree;
    }

    /**
     * @return array<string, string>
     */
    private function getKeyValueMap(): array
    {
        return [
            'key' => 'id',
            'value' => 'id',
            'title' => 'name',
        ];
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, string> $keyValueMap
     * @return array<string, mixed>
     */
    private function mapItemKeys(array $item, array $keyValueMap): array
    {
        foreach ($keyValueMap as $targetKey => $sourceKey) {
            if (!isset($item[$targetKey]) && isset($item[$sourceKey])) {
                $item[$targetKey] = $item[$sourceKey];
            }
        }

        return $item;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function processChildrenRecursively(array $item): array
    {
        if (isset($item['children']) && is_array($item['children']) && [] !== $item['children']) {
            $item['children'] = $this->appendKeyValue($item['children']);
        }

        return $item;
    }
}
