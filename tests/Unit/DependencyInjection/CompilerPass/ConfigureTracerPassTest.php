<?php

namespace Softspring\GoogleCloudTraceBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Doctrine\DBAL\Driver\Middleware;
use PHPUnit\Framework\TestCase;
use Softspring\GoogleCloudTraceBundle\DependencyInjection\CompilerPass\ConfigureTracerPass;
use Softspring\GoogleCloudTraceBundle\EventDispatcher\EventDispatcherTracerDecorator;
use Softspring\GoogleCloudTraceBundle\HttpCache\HttpCacheTracer;
use Softspring\GoogleCloudTraceBundle\Kernel\HttpKernelTracerDecorator;
use Softspring\GoogleCloudTraceBundle\Twig\EnvironmentTracer;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfigureTracerPassTest extends TestCase
{
    public function testProcessRegistersKernelAndEventDispatcherDecorators(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('http_kernel', new Definition(stdClass::class));
        $container->setDefinition('event_dispatcher', new Definition(stdClass::class));

        (new ConfigureTracerPass())->process($container);

        self::assertTrue($container->hasDefinition('sfs_gcloud_tracer.http_kernel'));
        self::assertTrue($container->hasDefinition('sfs_gcloud_tracer.event_dispatcher'));

        $kernelDefinition = $container->getDefinition('sfs_gcloud_tracer.http_kernel');
        self::assertSame(HttpKernelTracerDecorator::class, $kernelDefinition->getClass());
        self::assertSame('http_kernel', $kernelDefinition->getDecoratedService()[0]);

        $dispatcherDefinition = $container->getDefinition('sfs_gcloud_tracer.event_dispatcher');
        self::assertSame(EventDispatcherTracerDecorator::class, $dispatcherDefinition->getClass());
        self::assertSame('event_dispatcher', $dispatcherDefinition->getDecoratedService()[0]);
    }

    public function testProcessChangesTwigAndHttpCacheClassesWhenPresent(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('http_kernel', new Definition(stdClass::class));
        $container->setDefinition('event_dispatcher', new Definition(stdClass::class));
        $container->setDefinition('twig', new Definition(stdClass::class));
        $container->setDefinition('http_cache', new Definition(stdClass::class));

        (new ConfigureTracerPass())->process($container);

        self::assertSame(EnvironmentTracer::class, $container->getDefinition('twig')->getClass());
        self::assertSame(HttpCacheTracer::class, $container->getDefinition('http_cache')->getClass());
    }

    public function testProcessAddsDoctrineIntegration(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('http_kernel', new Definition(stdClass::class));
        $container->setDefinition('event_dispatcher', new Definition(stdClass::class));
        $container->setDefinition('doctrine', new Definition(stdClass::class));
        $container->setDefinition('doctrine.dbal.logger', new Definition(stdClass::class));

        (new ConfigureTracerPass())->process($container);

        if (interface_exists(Middleware::class)) {
            self::assertTrue($container->hasDefinition('sfs_gcloud_tracer.doctrine.dbal.connection_tracer_middleware'));
            self::assertSame(
                'doctrine.middleware',
                $container->getDefinition('sfs_gcloud_tracer.doctrine.dbal.connection_tracer_middleware')->getTag('doctrine.middleware')[0]['name'] ?? 'doctrine.middleware'
            );
        } else {
            self::assertTrue($container->hasDefinition('sfs_gcloud_tracer.doctrine.dbal.logger_decorator'));
            self::assertSame(
                'doctrine.dbal.logger',
                $container->getDefinition('sfs_gcloud_tracer.doctrine.dbal.logger_decorator')->getDecoratedService()[0]
            );
        }
    }
}
