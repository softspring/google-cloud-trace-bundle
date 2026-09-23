<?php

declare(strict_types=1);

namespace Softspring\GoogleCloudTraceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sfs_google_cloud_trace');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        /** @var NodeBuilder $children */
        $children = $rootNode->children();

        $children->booleanNode('enabled')->defaultTrue()->end();

        $instrumentation = $children->arrayNode('instrumentation')->addDefaultsIfNotSet();
        $instrumentationChildren = $instrumentation->children();
        $instrumentationChildren->booleanNode('kernel')->defaultTrue()->end();
        $instrumentationChildren->booleanNode('event_dispatcher')->defaultFalse()->end();
        $instrumentationChildren->booleanNode('twig')->defaultFalse()->end();
        $instrumentationChildren->booleanNode('doctrine')->defaultFalse()->end();
        $instrumentationChildren->booleanNode('http_cache')->defaultFalse()->end();

        $doctrine = $children->arrayNode('doctrine')->addDefaultsIfNotSet();
        $doctrineChildren = $doctrine->children();
        $doctrineChildren->booleanNode('include_sql')->defaultFalse()->end();

        return $treeBuilder;
    }
}
