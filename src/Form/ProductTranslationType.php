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
                'label' => ('admin.product.language'|trans)
            ])
            ->add('name', TextType::class, [
                'label' => ('admin.product.product_name'|trans),
                'required' => true,
                'attr' => [
                    'class' => 'form-control form-control-lg',
                    'placeholder' => ('admin.product.name_placeholder'|trans)
                ]
            ])
            ->add('shortDescription', TextType::class, [
                'label' => ('admin.product.short_description'|trans),
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => ('admin.product.short_description_placeholder'|trans)
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => ('admin.product.full_description'|trans),
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => ('admin.product.description_placeholder'|trans)
                ]
            ])
            ->add('concept', TextareaType::class, [
                'label' => ('admin.product.concept_design'|trans),
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => ('admin.product.concept_placeholder'|trans)
                ]
            ])
            ->add('materialsDetail', TextareaType::class, [
                'label' => ('admin.product.materials_used'|trans),
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => ('admin.product.materials_detail_placeholder'|trans)
                ]
            ])
            ->add('equipmentDetail', TextareaType::class, [
                'label' => ('admin.product.equipment_included'|trans),
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => ('admin.product.equipment_detail_placeholder'|trans)
                ]
            ])
            ->add('performanceDetails', TextareaType::class, [
                'label' => ('admin.product.performance'|trans),
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => ('admin.product.performance_placeholder'|trans)
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