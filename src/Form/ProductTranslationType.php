<?php

namespace App\Form;

use App\Entity\Language;
use App\Entity\ProductTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
                'label' => 'Langue'
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'required' => true,
                'attr' => [
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Ex: Produit MODUS Premium'
                ]
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'Description courte',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Résumé en une phrase'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description complète',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Description détaillée du produit...'
                ]
            ])
            ->add('concept', TextareaType::class, [
                'label' => 'Concept & Design',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Concept et aspects design du produit...'
                ]
            ])
            ->add('materialsDetail', TextareaType::class, [
                'label' => 'Matériaux utilisés',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Détails sur les matériaux et leur qualité...'
                ]
            ])
            ->add('equipmentDetail', TextareaType::class, [
                'label' => 'Équipements inclus',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Liste détaillée des équipements fournis...'
                ]
            ])
            ->add('performanceDetails', TextareaType::class, [
                'label' => 'Performances',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Spécifications techniques et performances...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductTranslation::class,
        ]);
    }
}