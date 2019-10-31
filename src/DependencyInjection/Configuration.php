<?php declare(strict_types=1);

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
        $treeBuilder = new TreeBuilder('oa_rating');
        $reflection = new \ReflectionClass(Vote::class);

        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('strategy')
                    ->defaultValue(Vote::IP_TYPE)
                    ->validate()
                        ->ifTrue(function ($value) use ($reflection) {
                            return !in_array($value, $reflection->getConstants(), true);
                        })
                        ->thenInvalid('Unrecognized strategy, expected one of: '. implode(', ', $reflection->getConstants()))
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
