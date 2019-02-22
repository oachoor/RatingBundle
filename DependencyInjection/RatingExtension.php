<?php

declare(strict_types=1);

namespace RatingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RatingExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $key => $value) {
            $container->setParameter("oa_rating.$key", $value);
        }
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $resources = $container->getParameter('twig.form.resources');
        $resources = array_merge(['RatingBundle:form:rating_widget.html.twig'], $resources);
        $container->setParameter('twig.form.resources', $resources);
    }

    /**
     * The extension alias
     *
     * @return string
     */
    public function getAlias()
    {
        return 'oa_rating';
    }
}
