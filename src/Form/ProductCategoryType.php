<?php

namespace App\Form;

use App\Entity\ProductCategory;
use App\Entity\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'admin.category.code',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'admin.category.code_placeholder',
                    'pattern' => '^[A-Za-z0-9_-]+$',
                    'title' => 'admin.category.code_title'
                ],
                'help' => 'admin.category.code_help'
            ])

            ->add('isActive', CheckboxType::class, [
                'label' => 'admin.category.active_category',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label']
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'admin.category.featured_category',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label']
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'admin.category.sort_order',
                'data' => 0,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'admin.category.sort_order_placeholder'
                ],
                'help' => 'admin.category.sort_order_help'
            ])
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
                'label' => 'admin.common.save',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg me-2'
                ]
            ])
            ->add('save_and_continue', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
                'label' => 'admin.common.save_and_continue',
                'attr' => [
                    'class' => 'btn btn-secondary'
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductCategory::class,
        ]);
    }
}