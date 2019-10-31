<?php declare(strict_types=1);

namespace RatingBundle\Form;

use RatingBundle\Model\AbstractRating;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class RatingType
 * @package RatingBundle\Form\RatingType
 */
class RatingType extends AbstractType
{
    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars = array_replace($view->vars, [
            'stars' => $options['stars']
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'rating'],
            'stars' => (int) AbstractRating::MAX_VALUE,
            'scale' => 1
        ]);
    }

    /**
     * @return string|AbstractType
     */
    public function getParent(): string
    {
        return NumberType::class;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'rating';
    }
}
