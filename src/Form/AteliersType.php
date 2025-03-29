<?php

namespace App\Form;

use App\Entity\Ateliers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class AteliersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Titre de l\'atelier',
                ],
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Description de l\'atelier',
                ],
                'required' => true,
            ])
            ->add('date', DateTimeType::class, [
                'label' => false,
                'html5' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Date de l\'atelier',
                ],
            ])
            ->add('placesMax', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Nombre de places disponibles',
                ],
                'required' => true,
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Créer',
                'attr' => [
                    'class' => 'btn btn-outline-primary mt-3',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ateliers::class,
        ]);
    }
}
