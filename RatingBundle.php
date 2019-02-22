<?php

namespace RatingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use RatingBundle\DependencyInjection\RatingExtension;

/**
 * Class RatingBundle
 * @package RatingBundle
 */
class RatingBundle extends Bundle
{
    /**
     * @return RatingExtension|\Symfony\Component\DependencyInjection\Extension\ExtensionInterface|null
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new RatingExtension();
        }

        return $this->extension;
    }
}
