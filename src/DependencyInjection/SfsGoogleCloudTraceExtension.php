<?php

declare(strict_types=1);

namespace Softspring\GoogleCloudTraceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class SfsGoogleCloudTraceExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sfs_google_cloud_trace.enabled', $config['enabled']);
        $container->setParameter('sfs_google_cloud_trace.instrumentation.kernel', $config['instrumentation']['kernel']);
        $container->setParameter('sfs_google_cloud_trace.instrumentation.event_dispatcher', $config['instrumentation']['event_dispatcher']);
        $container->setParameter('sfs_google_cloud_trace.instrumentation.twig', $config['instrumentation']['twig']);
        $container->setParameter('sfs_google_cloud_trace.instrumentation.doctrine', $config['instrumentation']['doctrine']);
        $container->setParameter('sfs_google_cloud_trace.instrumentation.http_cache', $config['instrumentation']['http_cache']);
        $container->setParameter('sfs_google_cloud_trace.doctrine.include_sql', $config['doctrine']['include_sql']);
    }
}
