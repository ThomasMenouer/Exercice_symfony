<?php

namespace App\Form\Admin;

use App\Entity\Ateliers;
use App\Entity\Participants;
use App\Repository\AteliersRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ParticipantsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $atelier = $options['atelier'];
        

        $builder
            ->add('name', TextType::class,
                [
                    'label' => false,
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Entrez le nom',
                        'class' => 'form-control mb-3',
                    ],
                ]
            )
            ->add('email', EmailType::class, 
                [
                    'label' => false,
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Entrez l\'email',
                        'class' => 'form-control mb-3',
                    ],
                ]
            )
            ->add('atelier', EntityType::class, [
                'class' => Ateliers::class,
                'query_builder' => function (AteliersRepository $atelierRepository) use ($atelier) {
                    return $atelierRepository->createQueryBuilder('a')
                    ->orderBy('a.title', 'ASC');
                },
                'choice_label' => 'title',
                'label' => false,
                'placeholder' => 'Sélectionner un atelier',
                'required' => true,
                'attr' => [
                    'class' => 'form-control mb-3',
                ],
            ])
            ->add('inscription', SubmitType::class,
                [
                    'label' => 'S\'inscrire',
                    'attr' => [
                        'class' => 'btn btn-outline-primary',
                    ],
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participants::class,
            'atelier' => null,
        ]);
    }
}
