<?php

namespace Mapado\DoctrineBlenderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mapado_doctrine_blender');

        $rootNode
            ->fixXmlConfig('doctrine_external_association')
            ->children()
                ->arrayNode('doctrine_external_associations')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('source_object_manager')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('classname')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('property_name')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('reference_getter')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('reference_object_manager')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('reference_class')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
