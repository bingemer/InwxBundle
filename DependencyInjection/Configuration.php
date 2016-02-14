<?php
/**
 * Created by PhpStorm.
 * User: joerg
 * Date: 14.02.16
 * Time: 19:47
 */
namespace Bingemer\InwxBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bingemer_inwx');

        $rootNode
            ->children()
                ->scalarNode('username')->end()
                ->scalarNode('password')->end()
                ->scalarNode('url')->end()
                ->scalarNode('locale')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}