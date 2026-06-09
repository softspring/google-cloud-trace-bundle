<?php

declare(strict_types=1);

namespace Softspring\GoogleCloudTraceBundle\DependencyInjection\CompilerPass;

use Doctrine\DBAL\Driver\Middleware;
use Softspring\GoogleCloudTraceBundle\Doctrine\DBAL\Logging\DbalLoggerDecorator;
use Softspring\GoogleCloudTraceBundle\EventDispatcher\EventDispatcherTracerDecorator;
use Softspring\GoogleCloudTraceBundle\HttpCache\HttpCacheTracer;
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
        if (!$this->parameter($container, 'sfs_google_cloud_trace.enabled', true)) {
            return;
        }

        if ($this->parameter($container, 'sfs_google_cloud_trace.instrumentation.kernel', true)) {
            $httpKernelDecorator = new Definition(HttpKernelTracerDecorator::class);
            $httpKernelDecorator->setDecoratedService('http_kernel');
            $httpKernelDecorator->setAutowired(true);
            $httpKernelDecorator->setAutoconfigured(true);
            $container->setDefinition('sfs_gcloud_tracer.http_kernel', $httpKernelDecorator);
        }

        if ($this->parameter($container, 'sfs_google_cloud_trace.instrumentation.event_dispatcher', false)) {
            $eventDispatcherDecorator = new Definition(EventDispatcherTracerDecorator::class);
            $eventDispatcherDecorator->setDecoratedService('event_dispatcher');
            $eventDispatcherDecorator->setAutowired(true);
            $eventDispatcherDecorator->setAutoconfigured(true);
            $container->setDefinition('sfs_gcloud_tracer.event_dispatcher', $eventDispatcherDecorator);
        }

        if ($this->parameter($container, 'sfs_google_cloud_trace.instrumentation.twig', false) && $container->hasDefinition('twig')) {
            $twig = $container->getDefinition('twig');
            $twig->setClass(EnvironmentTracer::class);
        }

        if ($this->parameter($container, 'sfs_google_cloud_trace.instrumentation.http_cache', false) && $container->hasDefinition('http_cache')) {
            $httpCache = $container->getDefinition('http_cache');
            $httpCache->setClass(HttpCacheTracer::class);
        }

        if ($this->parameter($container, 'sfs_google_cloud_trace.instrumentation.doctrine', false) && $container->hasDefinition('doctrine')) {
            if (interface_exists(Middleware::class)) {
                $doctrineMiddleware = new Definition(ConnectionTracerMiddleware::class);
                $doctrineMiddleware->addMethodCall('setIncludeSql', [$this->parameter($container, 'sfs_google_cloud_trace.doctrine.include_sql', false)]);
                $doctrineMiddleware->addTag('doctrine.middleware', ['priority' => 1000]);
                $container->setDefinition('sfs_gcloud_tracer.doctrine.dbal.connection_tracer_middleware', $doctrineMiddleware);
            } elseif ($container->hasDefinition('doctrine.dbal.logger')) {
                $loggerDecorator = new Definition(DbalLoggerDecorator::class);
                $loggerDecorator->setAutowired(true);
                $loggerDecorator->setDecoratedService('doctrine.dbal.logger');
                $loggerDecorator->addMethodCall('setIncludeSql', [$this->parameter($container, 'sfs_google_cloud_trace.doctrine.include_sql', false)]);
                $container->setDefinition('sfs_gcloud_tracer.doctrine.dbal.logger_decorator', $loggerDecorator);
            }
        }
    }

    private function parameter(ContainerBuilder $container, string $name, bool $default): bool
    {
        return $container->hasParameter($name) ? (bool) $container->getParameter($name) : $default;
    }
}
