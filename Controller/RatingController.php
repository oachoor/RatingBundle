<?php

declare(strict_types=1);

namespace RatingBundle\Controller;

use RatingBundle\Entity\Vote;
use Symfony\Component\Form\Form;
use RatingBundle\Form\RatingType;
use RatingBundle\Model\AbstractRating;
use RatingBundle\Repository\VoteRepository;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use RatingBundle\Repository\RatingRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class RatingController
 * @Route("/rating")
 * @package RatingBundle\Controller
 */
class RatingController extends Controller
{
    /**
     * @Template()
     * @Route("/view", methods={"GET"})
     *
     * @return array|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction()
    {
        return [];
    }

    /**
     * @Route("/vote/{contentId}", requirements={"contentId"="\d+"}, methods={"POST"})
     *
     * @param Request $request
     * @param int $contentId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function voteAction(Request $request, int $contentId)
    {
        $response = new Response();
        $response->mustRevalidate();
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);

        $form = $this->get('form.factory')
            ->createNamedBuilder('form__rating')
            ->setAction($this->generateUrl('rating_rating_vote', ['contentId' => $contentId]))
            ->setMethod('POST')
            ->add('rating', RatingType::class, [
                'label' => 'Rating',
                'constraints' => [new NotBlank()]
            ])
            ->getForm();

        $ratingRepository = $this->get(RatingRepository::class);
        $rating = $ratingRepository->findOneByContentId($contentId);
        $hasVoted = $this->hasVoted($request, $contentId);

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $form->handleRequest($request);
            if (!$form->isValid() && count($errors = self::getErrorsAsArray($form)) > 0) {
                return new JsonResponse($errors);
            }
            if (null === $rating) {
                $rating = $ratingRepository->store($contentId);
            }

            if (!$hasVoted) {
                if ($this->isCookieBased()) {
                    $cookieName = $this->getParameter('oa_rating.cookie_name');
                    $ratedContentIds = (array) json_decode($request->cookies->get($cookieName), true);
                    $ratedContentIds[] = $rating->getContentId();
                    $cookie = new Cookie(
                        $cookieName,
                        json_encode(array_filter(array_unique($ratedContentIds))),
                        strtotime($this->getParameter('oa_rating.cookie_lifetime'))
                    );
                    $response->headers->setCookie($cookie);
                }
                $this->get(VoteRepository::class)->create($rating, [
                    'rating' => $form->get('rating')->getData(),
                    'ip' => $this->getIp() # $request->getClientIp()
                ]);
                $hasVoted = true;
            }
        }

        return $this->render(sprintf('RatingBundle:rating:%s.html.twig', $hasVoted ? 'result' : 'rate'), [
            'form' => $form->createView(),
            'max' => (int) AbstractRating::MAX_VALUE,
            'rating' => $ratingRepository->findOneByContentId($contentId)
        ], $response);
    }

    /**
     * @Template()
     * @Route("/result/{contentId}", requirements={"contentId"="\d+"}, methods={"GET"})
     *
     * @param int $contentId
     *
     * @return array|\Symfony\Component\HttpFoundation\Response
     */
    public function resultAction(int $contentId)
    {
        $ratingRepository = $this->get(RatingRepository::class);

        if (null === ($rating = $ratingRepository->findOneByContentId($contentId))) {
            throw new NotFoundHttpException('Rating not found with given Id.');
        }

        return ['max' => (int) AbstractRating::MAX_VALUE, 'rating' => $rating];
    }

    /**
     * Checks whether a user has already voted.
     *
     * @param Request $request
     * @param int $contentId
     *
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function hasVoted(Request $request, int $contentId): bool
    {
        $cookieName = $this->getParameter('oa_rating.cookie_name');
        if ($this->isCookieBased() && $request->cookies->has($cookieName)) {
            return in_array($contentId, (array) json_decode($request->cookies->get($cookieName), true), true);
        }

        return $this->get(VoteRepository::class)->hasVoted($contentId, $this->getIp());
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     *
     * @return array
     */
    private static function getErrorsAsArray(Form $form): array
    {
        $errors = [];

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['root'][] = $error->getMessage();
                continue;
            }
            $errors[] = $error->getMessage();
        }

        array_map(function ($child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = self::getErrorsAsArray($child);
            }
        }, $form->all());

        return $errors;
    }

    /**
     * @return mixed
     */
    private function getIp()
    {
        return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    }

    /**
     * @return bool
     */
    private function isCookieBased(): bool
    {
        return $this->getParameter('oa_rating.strategy') === Vote::COOKIE_TYPE;
    }
}
