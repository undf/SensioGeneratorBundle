<?php

namespace Sensio\Bundle\GeneratorBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('sensio_generator');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->enumNode('label_strategy')
                    ->defaultValue('undf.generator.label.strategy.underscore')
                    ->values(array(
                        'undf.generator.label.strategy.bc',
                        'undf.generator.label.strategy.underscore',
                        'undf.generator.label.strategy.native',
                        'undf.generator.label.strategy.noop',
                        'undf.generator.label.strategy.form_component'
                    ))
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
