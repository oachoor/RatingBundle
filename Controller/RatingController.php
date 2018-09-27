<?php

declare(strict_types=1);

namespace RatingBundle\Controller;

use Symfony\Component\Form\Form;
use RatingBundle\Form\RatingType;
use RatingBundle\Model\AbstractRating;
use RatingBundle\Repository\VoteRepository;
use Symfony\Component\HttpFoundation\Request;
use RatingBundle\Repository\RatingRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
     * @Route("/view")
     * @Method({"GET"})
     *
     * @return array|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction()
    {
        return [];
    }

    /**
     * @Route("/vote/{contentId}", requirements={"contentId"="\d+"})
     * @Method({"POST"})
     *
     * @param Request $request
     * @param int $contentId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function voteAction(Request $request, int $contentId)
    {
        $form = $this->get('form.factory')
            ->createNamedBuilder('form__rating')
            ->setAction($this->generateUrl('rating_rating_vote', ['contentId' => $contentId]))
            ->setMethod('POST')
            ->add('rating', RatingType::class, [
                'label' => 'Rating',
                'constraints' => [new NotBlank()]
            ])
            ->getForm();

        $voteRepository = $this->get(VoteRepository::class);
        $ratingRepository = $this->get(RatingRepository::class);
        $rating = $ratingRepository->findOneByContentId($contentId);
        $ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $form->handleRequest($request);
            if (!$form->isValid() && count($errors = self::getErrorsAsArray($form)) > 0) {
                return new JsonResponse($errors);
            }
            if (null === $rating) {
                $rating = $ratingRepository->store($contentId);
            }
            !$voteRepository->hasVoted($contentId, $ip) && $this->get(VoteRepository::class)->create($rating, [
                'rating' => $form->get('rating')->getData(),
                'ip' => $ip # $request->getClientIp()
            ]);
        }

        $hasVoted = $voteRepository->hasVoted($contentId, $ip);

        return $this->render(sprintf('RatingBundle:rating:%s.html.twig', $hasVoted ? 'result' : 'rate'), [
            'form' => $form->createView(),
            'max' => (int) AbstractRating::MAX_VALUE,
            'rating' => $ratingRepository->findOneByContentId($contentId)
        ]);
    }

    /**
     * @Template()
     * @Route("/result/{contentId}", requirements={"contentId"="\d+"})
     * @Method({"GET"})
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
}
