<?php

declare(strict_types=1);

namespace Softspring\GoogleCloudTraceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sfs_google_cloud_trace');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        /* @phpstan-ignore-next-line Symfony Config's fluent builder keeps the concrete node type at runtime. */
        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->arrayNode('instrumentation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('kernel')->defaultTrue()->end()
                        ->booleanNode('event_dispatcher')->defaultFalse()->end()
                        ->booleanNode('twig')->defaultFalse()->end()
                        ->booleanNode('doctrine')->defaultFalse()->end()
                        ->booleanNode('http_cache')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('doctrine')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('include_sql')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
