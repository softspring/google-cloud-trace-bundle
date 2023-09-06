<?php

namespace Softspring\GoogleCloudTraceBundle\DependencyInjection\CompilerPass;

use Doctrine\DBAL\Driver\Middleware;
use Softspring\GoogleCloudTraceBundle\Doctrine\DBAL\Logging\QueryTracerLogger;
use Softspring\GoogleCloudTraceBundle\EventDispatcher\EventDispatcherTracerDecorator;
use Softspring\GoogleCloudTraceBundle\Kernel\HttpKernelTracerDecorator;
use Softspring\GoogleCloudTraceBundle\Middleware\ConnectionTracerMiddleware;
use Softspring\GoogleCloudTraceBundle\Twig\EnvironmentTracer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfigureTracerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $httpKernelDecorator = new Definition(HttpKernelTracerDecorator::class);
        $httpKernelDecorator->setDecoratedService('http_kernel');
        $httpKernelDecorator->setAutowired(true);
        $httpKernelDecorator->setAutoconfigured(true);
        $container->setDefinition('sfs_gcloud_tracer.http_kernel', $httpKernelDecorator);

        $eventDispatcherDecorator = new Definition(EventDispatcherTracerDecorator::class);
        $eventDispatcherDecorator->setDecoratedService('event_dispatcher');
        $eventDispatcherDecorator->setAutowired(true);
        $eventDispatcherDecorator->setAutoconfigured(true);
        $container->setDefinition('sfs_gcloud_tracer.event_dispatcher', $eventDispatcherDecorator);

        if ($container->hasDefinition('twig')) {
            $twig = $container->getDefinition('twig');
            $twig->setClass(EnvironmentTracer::class);
        }

        if ($container->hasDefinition('doctrine')) {
            if (interface_exists(Middleware::class)) {
                $doctrineMiddleware = new Definition(ConnectionTracerMiddleware::class);
                $doctrineMiddleware->addTag('doctrine.middleware', ['priority' => 1000]);
                $container->setDefinition('sfs_gcloud_tracer.doctrine.dbal.connection_tracer_middleware', $doctrineMiddleware);
            } else {
                $queryTracerLogger = new Definition(QueryTracerLogger::class);
                $queryTracerLogger->setAutowired(true);
                $container->setDefinition('sfs_gcloud_tracer.doctrine.dbal.logger', $queryTracerLogger);

                $chainLogger = $container->getDefinition('doctrine.dbal.logger.chain');
                $loggers = $chainLogger->getArgument(0);
                $loggers[] = $queryTracerLogger;
                $chainLogger->setArgument(0, $loggers);
            }
        }
    }
}
