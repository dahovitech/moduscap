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
                'label' => 'Nom',
                'required' => true
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'Description courte',
                'required' => false
            ])
            ->add('concept', TextareaType::class, [
                'label' => 'Concept & Design',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('materialsDetail', TextareaType::class, [
                'label' => 'Détail matériaux',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('equipmentDetail', TextareaType::class, [
                'label' => 'Détail équipements',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('performanceDetails', TextareaType::class, [
                'label' => 'Détails performances',
                'required' => false,
                'attr' => ['rows' => 3]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductTranslation::class,
        ]);
    }
}