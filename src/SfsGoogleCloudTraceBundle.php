<?php

namespace Softspring\GoogleCloudTraceBundle;

use Softspring\GoogleCloudTraceBundle\DependencyInjection\CompilerPass\ConfigureTracerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SfsGoogleCloudTraceBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ConfigureTracerPass());
    }
}
