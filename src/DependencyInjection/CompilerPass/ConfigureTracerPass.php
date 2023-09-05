<?php

namespace Softspring\GoogleCloudTraceBundle\DependencyInjection\CompilerPass;

use Softspring\GoogleCloudTraceBundle\Twig\EnvironmentTracer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigureTracerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('twig')) {
            return;
        }

        $twig = $container->getDefinition('twig');
        $twig->setClass(EnvironmentTracer::class);
    }
}
