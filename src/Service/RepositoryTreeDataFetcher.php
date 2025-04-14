<?php

namespace Tourze\EasyAdminExtraBundle\Service;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
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

    public function genTreeData(): array
    {
        $_repo = $this->registry
            ->getManagerForClass($this->getEntityClass())
            ->getRepository($this->getEntityClass());

        $tree = [];
        // 先查找第一层
        foreach ($_repo->findBy(['parent' => null]) as $item) {
            $tree[] = $this->normalizer->normalize($item, 'array', [
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
                'groups' => 'api_tree',
            ]);
        }

        return $this->appendKeyValue($tree);
    }

    public function checkIfChildrenEmpty($item): bool
    {
        if (!isset($item['children'])) {
            return true;
        }

        if (empty($item['children'])) {
            return true;
        }

        return false;
    }

    public function checkTree(array $tree, $level): array
    {
        foreach ($tree as $k => &$item) {
            if ($this->checkIfChildrenEmpty($item) && 1 !== $level) {
                unset($tree[$k]);
                continue;
            }

            if (isset($item['children'])) {
                $item['children'] = $this->checkTree($item['children'], $level + 1);
            }
        }

        unset($item);

        return array_values($tree);
    }

    public function appendKeyValue(array $tree): array
    {
        $map = [
            'key' => 'id',
            'value' => 'id',
            'title' => 'name',
        ];

        foreach ($tree as $k => &$item) {
            foreach ($map as $_k => $_v) {
                if (!isset($item[$_k]) && isset($item[$_v])) {
                    $item[$_k] = $item[$_v];
                }
            }

            if (isset($item['children']) && $item['children']) {
                $item['children'] = $this->appendKeyValue($item['children']);
            }
        }

        unset($item);

        return array_values($tree);
    }
}
