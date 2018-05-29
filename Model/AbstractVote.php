<?php

declare(strict_types=1);

namespace RatingBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use RatingBundle\Entity\Traits\TimestampTrait;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AbstractVote
 * @package RatingBundle\Model
 */
abstract class AbstractVote
{
    use TimestampTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    protected $value = AbstractRating::MAX_VALUE;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    protected $ip = 0;

    /**
     * @var AbstractRating
     *
     * @ORM\ManyToOne(targetEntity="RatingBundle\Model\AbstractRating", inversedBy="votes")
     */
    protected $rating;

    /**
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="Symfony\Component\Security\Core\User\UserInterface")
     */
    protected $voter;

    /**
     * AbstractVote constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param string $ip
     * @return $this
     */
    public function setIp($ip): self
    {
        $this->ip = ip2long($ip);

        return $this;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return long2ip($this->ip);
    }

    /**
     * @param int $ip
     * @return $this
     */
    public function setIntIp($ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @param AbstractRating $rating
     * @return $this
     */
    public function setRating(AbstractRating $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return AbstractRating
     */
    public function getRating(): ?AbstractRating
    {
        return $this->rating;
    }

    /**
     * @param UserInterface $voter
     * @return $this
     */
    public function setVoter($voter): self
    {
        $this->voter = $voter;

        return $this;
    }

    /**
     * @return UserInterface
     */
    public function getVoter(): ?UserInterface
    {
        return $this->voter;
    }
}
