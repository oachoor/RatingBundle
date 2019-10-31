<?php declare(strict_types=1);

namespace RatingBundle\Controller;

use RatingBundle\Entity\Vote;
use Symfony\Component\Form\Form;
use RatingBundle\Form\RatingType;
use RatingBundle\Model\AbstractRating;
use Symfony\Component\Form\FormFactory;
use RatingBundle\Repository\VoteRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use RatingBundle\Repository\RatingRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class RatingController
 * @Route("/rating")
 * @package RatingBundle\Controller
 */
final class RatingController extends AbstractController
{
    /** @var FormFactory */
    private $formFactory;

    /** @var RatingRepository */
    private $ratingRepository;

    /** @var VoteRepository */
    private $voteRepository;

    /** @var string */
    private $cookieName;

    /** @var string */
    private $cookieStrategy;

    /** @var int */
    private $cookieLifetime;

    /**
     * RatingController constructor.
     *
     * @param FormFactoryInterface  $formFactory
     * @param RatingRepository      $ratingRepository
     * @param VoteRepository        $voteRepository
     * @param string                $cookieName
     * @param string                $cookieStrategy
     * @param int                   $cookieLifetime
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        RatingRepository $ratingRepository,
        VoteRepository $voteRepository,
        string $cookieName,
        string $cookieStrategy,
        int $cookieLifetime)
    {
        $this->formFactory = $formFactory;
        $this->ratingRepository = $ratingRepository;
        $this->voteRepository = $voteRepository;
        $this->cookieName = $cookieName;
        $this->cookieStrategy = $cookieStrategy;
        $this->cookieLifetime = $cookieLifetime;
    }

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

        $form = $this->formFactory
            ->createNamedBuilder('form__rating', FormType::class, null, ['csrf_protection' => false])
            ->setAction($this->generateUrl('rating_rating_vote', ['contentId' => $contentId]))
            ->setMethod('POST')
            ->add('rating', RatingType::class, [
                'label' => 'Rating',
                'constraints' => [new NotBlank()]
            ])
            ->getForm();

        $rating = $this->ratingRepository->findOneByContentId($contentId);
        $hasVoted = $this->hasVoted($request, $contentId);

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $form->handleRequest($request);
            if (!$form->isValid() && count($errors = self::getErrorsAsArray($form)) > 0) {
                return new JsonResponse($errors);
            }
            if (null === $rating) {
                $rating = $this->ratingRepository->store($contentId);
            }

            if (!$hasVoted) {
                if ($this->isCookieBased()) {
                    $ratedContentIds = [];
                    if ($request->cookies->has($this->cookieName)) {
                        $ratedContentIds = (array) json_decode($request->cookies->get($this->cookieName), true);
                    }
                    $ratedContentIds[] = $rating->getContentId();
                    $cookie = new Cookie(
                        $this->cookieName,
                        json_encode(array_filter(array_unique($ratedContentIds))),
                        strtotime($this->cookieLifetime)
                    );
                    $response->headers->setCookie($cookie);
                }
                $this->voteRepository->create($rating, [
                    'rating' => $form->get('rating')->getData(),
                    'ip' => $this->getIp() # $request->getClientIp()
                ]);
                $hasVoted = true;
            }
        }

        return $this->render(sprintf('RatingBundle:rating:%s.html.twig', $hasVoted ? 'result' : 'rate'), [
            'form' => $form->createView(),
            'max' => (int) AbstractRating::MAX_VALUE,
            'rating' => $this->ratingRepository->findOneByContentId($contentId)
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
        if (null === ($rating = $this->ratingRepository->findOneByContentId($contentId))) {
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
        if ($this->isCookieBased()) {
            if ($request->cookies->has($this->cookieName)) {
                return in_array($contentId, (array) json_decode($request->cookies->get($this->cookieName), true), true);
            }
            return false;
        }

        return $this->voteRepository->hasVoted($contentId, $this->getIp());
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
        return $this->cookieStrategy === Vote::COOKIE_TYPE;
    }
}
