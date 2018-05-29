<?php

declare(strict_types=1);

namespace RatingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RatingBundle\Model\AbstractVote as BaseVote;

/**
 * @ORM\Table(name="vote")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="RatingBundle\Repository\VoteRepository")
 */
class Vote extends BaseVote
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
     * Vote constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @ORM\PreFlush()
     * @ORM\PostUpdate()
     * @ORM\PostPersist()
     */
    public function updatedVotes(): void
    {
        if (null !== ($rating = $this->getRating())) {
            $rating->recalculate();
        }
    }
}
