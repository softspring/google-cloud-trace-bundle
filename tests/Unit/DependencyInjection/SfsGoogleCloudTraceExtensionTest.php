<?php

declare(strict_types=1);

namespace Softspring\GoogleCloudTraceBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Softspring\GoogleCloudTraceBundle\DependencyInjection\SfsGoogleCloudTraceExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SfsGoogleCloudTraceExtensionTest extends TestCase
{
    public function testLoadRegistersConservativeDefaultConfiguration(): void
    {
        $container = new ContainerBuilder();

        (new SfsGoogleCloudTraceExtension())->load([], $container);

        self::assertTrue($container->getParameter('sfs_google_cloud_trace.enabled'));
        self::assertTrue($container->getParameter('sfs_google_cloud_trace.instrumentation.kernel'));
        self::assertFalse($container->getParameter('sfs_google_cloud_trace.instrumentation.event_dispatcher'));
        self::assertFalse($container->getParameter('sfs_google_cloud_trace.instrumentation.twig'));
        self::assertFalse($container->getParameter('sfs_google_cloud_trace.instrumentation.doctrine'));
        self::assertFalse($container->getParameter('sfs_google_cloud_trace.instrumentation.http_cache'));
        self::assertFalse($container->getParameter('sfs_google_cloud_trace.doctrine.include_sql'));
    }

    public function testLoadAcceptsExplicitInstrumentationConfiguration(): void
    {
        $container = new ContainerBuilder();

        (new SfsGoogleCloudTraceExtension())->load([
            [
                'enabled' => true,
                'instrumentation' => [
                    'event_dispatcher' => true,
                    'twig' => true,
                    'doctrine' => true,
                    'http_cache' => true,
                ],
                'doctrine' => [
                    'include_sql' => true,
                ],
            ],
        ], $container);

        self::assertTrue($container->getParameter('sfs_google_cloud_trace.instrumentation.event_dispatcher'));
        self::assertTrue($container->getParameter('sfs_google_cloud_trace.instrumentation.twig'));
        self::assertTrue($container->getParameter('sfs_google_cloud_trace.instrumentation.doctrine'));
        self::assertTrue($container->getParameter('sfs_google_cloud_trace.instrumentation.http_cache'));
        self::assertTrue($container->getParameter('sfs_google_cloud_trace.doctrine.include_sql'));
    }
}
