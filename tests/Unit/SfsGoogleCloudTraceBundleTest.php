<?php

namespace Softspring\GoogleCloudTraceBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Softspring\GoogleCloudTraceBundle\DependencyInjection\CompilerPass\ConfigureTracerPass;
use Softspring\GoogleCloudTraceBundle\SfsGoogleCloudTraceBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SfsGoogleCloudTraceBundleTest extends TestCase
{
    public function testBuildRegistersCompilerPass(): void
    {
        $bundle = new SfsGoogleCloudTraceBundle();
        $container = new ContainerBuilder();

        $bundle->build($container);

        $passes = $container->getCompilerPassConfig()->getBeforeOptimizationPasses();
        $found = false;

        foreach ($passes as $pass) {
            if ($pass instanceof ConfigureTracerPass) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);
    }
}
