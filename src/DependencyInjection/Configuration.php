<?php
namespace RinProject\FastCrudBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('fast_crud');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('exception_interceptor')
                ->children()
                    ->scalarNode('enabled')->defaultValue(false)->end()
                    ->scalarNode('effective_pattern')->defaultValue('/.*/')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}