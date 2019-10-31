<?php declare(strict_types=1);

namespace RatingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RatingBundle\Model\AbstractRating as BaseRating;

/**
 * @ORM\Table(name="rating")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="RatingBundle\Repository\RatingRepository")
 */
class Rating extends BaseRating
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Rating constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}
