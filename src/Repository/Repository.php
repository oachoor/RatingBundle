<?php declare(strict_types=1);

namespace RatingBundle\Repository;

/**
 * Interface Repository
 * @package RatingBundle\Repository
 */
interface Repository
{
    /**
     * @param int $id
     *
     * @return mixed
     */
    public function load(int $id);
}
