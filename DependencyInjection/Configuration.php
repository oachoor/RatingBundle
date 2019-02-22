<?php

namespace RatingBundle\DependencyInjection;

use RatingBundle\Entity\Vote;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oa_rating');
        $refl = new \ReflectionClass(Vote::class);
        
        $rootNode
            ->children()
                ->scalarNode('strategy')
                    ->defaultValue(Vote::IP_TYPE)
                    ->validate()
                        ->ifTrue(function ($value) use ($refl) {
                            return !in_array($value, $refl->getConstants(), true);
                        })
                        ->thenInvalid('Unrecognized strategy, expected one of: '. implode(', ', $refl->getConstants()))
                    ->end()
                ->end()
                ->scalarNode('cookie_name')
                    ->defaultValue('oa_rating_voted')
                ->end()
                ->scalarNode('cookie_lifetime')
                    ->defaultValue('+1 year')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
