<?php

namespace Tourze\EasyAdminExtraBundle;

use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\EcolBundle\EcolBundle;

class EasyAdminExtraBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            TwigBundle::class => ['all' => true],
            EasyAdminBundle::class => ['all' => true],
            EcolBundle::class => ['all' => true],
        ];
    }
}
