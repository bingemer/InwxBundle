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
                ->scalarNode('url')
                    ->defaultValue('https://api.ote.domrobot.com/xmlrpc/') // defaults to test environment
                ->end()
                ->scalarNode('locale')
                    ->defaultValue('en')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}