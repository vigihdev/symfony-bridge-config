<?php

declare(strict_types=1);

namespace Tests\Entity;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class AppConfig implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('app');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->scalarNode('env')->defaultValue('dev')->end()
            ->scalarNode('debug')->defaultTrue()->end()
            ->scalarNode('storage_path')->defaultValue('%kernel.project_dir%/storage')->end()
            ->arrayNode('database')
            ->children()
            ->scalarNode('driver')->defaultValue('mysql')->end()
            ->scalarNode('host')->defaultValue('127.0.0.1')->end()
            ->scalarNode('port')->defaultValue('3306')->end()
            ->scalarNode('user')->defaultValue('root')->end()
            ->scalarNode('password')->defaultValue('')->end()
            ->scalarNode('dbname')->defaultValue('app_db')->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
