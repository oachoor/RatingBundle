<?php

declare(strict_types=1);

namespace RatingBundle\Repository;

use RatingBundle\Entity\Vote;
use RatingBundle\Entity\Rating;
use Doctrine\ORM\EntityRepository;

/**
 * Class VoteRepository
 * @package RatingBundle\Repository
 */
final class VoteRepository extends EntityRepository implements Repository
{
    /**
     * @param Rating $rating
     * @param array $data
     *
     * @return Vote
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(Rating $rating, array $data): Vote
    {
        $vote = (new Vote())
            ->setValue($data['rating'])
            ->setIp($data['ip']);

        $rating->addVote($vote);
        $this->store($rating);

        return $vote;
    }

    /**
     * @param object $entity
     * @param bool $refresh
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function store($entity, bool $refresh = false)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);

        $refresh && $this->_em->refresh($entity);
    }

    /**
     * @param int $contentId
     * @param string|null $ip
     *
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function hasVoted(int $contentId, string $ip = null): bool
    {
        if ('' === (string) trim($ip)) {
            return false;
        }

        $qb = $this->createQueryBuilder('v');

        $query = $qb
            ->select('v.id')
            ->innerJoin('v.rating', 'r')
            ->where($qb->expr()->eq('v.ip', $qb->expr()->literal(ip2long($ip))))
            ->andWhere($qb->expr()->eq('r.contentId', $contentId))
            ->getQuery();

        return null !== $query->getOneOrNullResult();
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function load(int $id)
    {
        // TODO: Implement load() method.
    }
}
