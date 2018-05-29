<?php

declare(strict_types=1);

namespace RatingBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use RatingBundle\Model\AbstractVote;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class VoteSubscriber
 * @package RatingBundle\EventSubscriber
 */
class VoteSubscriber implements EventSubscriber
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * VoteSubscriber constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            'prePersist',
            'preUpdate'
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        if ($args->getEntity() instanceof AbstractVote) {
            $this->assignVoter($args->getEntity());
        }
    }

    /**
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(PreUpdateEventArgs $event): void
    {
        if ($event->getEntity() instanceof AbstractVote) {
            $this->assignVoter($event->getEntity());
        }
    }

    /**
     * @param AbstractVote $vote
     */
    private function assignVoter(AbstractVote $vote): void
    {
        if (null !== ($token = $this->tokenStorage->getToken())) {
            if ($token->getUser() instanceof UserInterface) {
                $vote->setVoter($token->getUser());
            }
        }
    }
}
