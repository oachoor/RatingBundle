<?php

declare(strict_types=1);

namespace RatingBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use RatingBundle\Entity\Traits\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class AbstractRating
 * @package RatingBundle\Model
 */
abstract class AbstractRating
{
    use TimestampTrait;

    /**
     * @const float
     */
    public const MAX_VALUE = 5.0;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var float
     *
     * @ORM\Column(name="total_values", type="float")
     */
    protected $totalValues = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="total_votes", type="integer")
     */
    protected $totalVotes = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="content_id", type="integer")
     */
    protected $contentId;

    /**
     * @var AbstractVote[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="RatingBundle\Model\AbstractVote", mappedBy="rating", cascade={"persist", "remove"})
     */
    protected $votes;

    /**
     * AbstractRating constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->votes = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return float The rating value
     */
    public function getValue(): float
    {
        return (float) 0 < $this->totalVotes ? $this->totalValues / $this->totalVotes : 0;
    }

    /**
     * @return float The relative rating value
     */
    public function getRelativeValue(): float
    {
        return (float) 0 < $this->totalVotes ? ($this->totalValues / $this->totalVotes) / self::MAX_VALUE : 0;
    }

    /**
     * @return float The percentage rating value
     */
    public function getPercentageValue(): float
    {
        return (float) 0 < $this->totalVotes ? ($this->totalValues / $this->totalVotes) / self::MAX_VALUE * 100 : 0;
    }

    /**
     * @return $this
     */
    public function recalculate(): self
    {
        $this->totalValues = 0;
        $this->totalVotes = $this->votes->count();

        foreach ($this->votes as $vote) {
            $this->totalValues += $vote->getValue();
        }

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalValues(): float
    {
        return $this->totalValues;
    }

    /**
     * @param float $totalValues
     * @return $this
     */
    public function setTotalValues($totalValues): self
    {
        $this->totalValues = $totalValues;

        return $this;
    }

    /**
     * @return int
     */
    public function getContentId(): int
    {
        return $this->contentId;
    }

    /**
     * @param int $contentId
     * @return $this
     */
    public function setContentId(int $contentId): self
    {
        $this->contentId = $contentId;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalVotes(): int
    {
        return $this->totalVotes;
    }

    /**
     * @param float $totalVotes
     * @return $this
     */
    public function setTotalVotes($totalVotes): self
    {
        $this->totalVotes = $totalVotes;

        return $this;
    }

    /**
     * @return AbstractVote[]|ArrayCollection
     */
    public function getVotes(): ArrayCollection
    {
        return $this->votes;
    }

    /**
     * @param AbstractVote $vote
     * @return $this
     */
    public function addVote(AbstractVote $vote): self
    {
        $vote->setRating($this);

        $this->votes->add($vote);

        return $this;
    }

    /**
     * @param AbstractVote[]|ArrayCollection $votes
     * @return $this
     */
    public function setVotes(ArrayCollection $votes): self
    {
        foreach ($votes as $vote) {
            $vote->setRating($this);
        }
        $this->votes = $votes;

        return $this;
    }
}
