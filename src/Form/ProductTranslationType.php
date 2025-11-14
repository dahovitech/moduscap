<?php

namespace App\Form;

use App\Entity\Language;
use App\Entity\ProductTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                'label' => 'admin.product.language'
            ])
            ->add('name', TextType::class, [
                'label' => 'admin.product.product_name',
                'required' => true,
                'attr' => [
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'admin.product.name_placeholder'
                ]
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'admin.product.short_description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'admin.product.short_description_placeholder'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'admin.product.full_description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'admin.product.description_placeholder'
                ]
            ])
            ->add('concept', TextareaType::class, [
                'label' => 'admin.product.concept_design',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'admin.product.concept_placeholder'
                ]
            ])
            ->add('materialsDetail', TextareaType::class, [
                'label' => 'admin.product.materials',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'admin.product.materials_placeholder'
                ]
            ])
            ->add('equipmentDetail', TextareaType::class, [
                'label' => 'admin.product.equipment',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'admin.product.equipment_placeholder'
                ]
            ])
            ->add('performanceDetails', TextareaType::class, [
                'label' => 'admin.product.performance',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'admin.product.performance_placeholder'
                ]
            ])
            ->add('specifications', TextareaType::class, [
                'label' => 'admin.product.specifications',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'admin.product.specifications_placeholder'
                ]
            ])
            ->add('advantages', TextareaType::class, [
                'label' => 'admin.product.advantages',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'admin.product.advantages_placeholder'
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