<?php

namespace Beerfranz\DynamicScheduleBundle\Tests;

use Beerfranz\DynamicScheduleBundle\DynamicScheduleBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Zenstruck\Messenger\Test\ZenstruckMessengerTestBundle;

class TestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new DynamicScheduleBundle(),
            new ZenstruckMessengerTestBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config.yaml');
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/var/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return __DIR__.'/var/log';
    }
}
