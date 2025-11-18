<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\ProductCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'admin.product.code',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'admin.product.code_placeholder',
                    'pattern' => '^[A-Za-z0-9_-]+$',
                    'title' => 'admin.product.code_title'
                ],
                'help' => 'admin.product.code_help'
            ])
            ->add('category', EntityType::class, [
                'class' => ProductCategory::class,
                'choice_label' => 'name',
                'label' => 'admin.product.category'
            ])
            ->add('basePrice', MoneyType::class, [
                'label' => 'admin.product.base_price',
                'currency' => 'EUR',
                'required' => false,
                'help' => 'admin.product.base_price_help'
            ])
            ->add('surface', TextType::class, [
                'label' => 'admin.product.surface',
                'required' => false
            ])
            ->add('dimensions', TextType::class, [
                'label' => 'admin.product.dimensions',
                'required' => false,
                'help' => 'admin.product.dimensions_help'
            ])
            ->add('rooms', IntegerType::class, [
                'label' => 'admin.product.rooms',
                'required' => false,
                'data' => 1
            ])
            ->add('height', IntegerType::class, [
                'label' => 'admin.product.height',
                'required' => false
            ])
            // Materials, equipment, specifications, advantages are now in ProductTranslation entity for multilingual support
            ->add('technicalSpecs', TextareaType::class, [
                'label' => 'admin.product.technical_specs_detailed',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('assemblyTime', IntegerType::class, [
                'label' => 'admin.product.assembly_time',
                'required' => false
            ])
            ->add('energyClass', ChoiceType::class, [
                'label' => 'admin.product.energy_class',
                'required' => false,
                'choices' => [
                    'A+++' => 'A+++',
                    'A++' => 'A++',
                    'A+' => 'A+',
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                    'E' => 'E'
                ]
            ])
            ->add('warrantyStructure', IntegerType::class, [
                'label' => 'admin.product.warranty_structure',
                'required' => false,
                'data' => 10
            ])
            ->add('warrantyEquipment', IntegerType::class, [
                'label' => 'admin.product.warranty_equipment',
                'required' => false,
                'data' => 5
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'admin.product.active_product',
                'required' => false
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'admin.product.featured',
                'required' => false
            ])
            ->add('isCustomizable', CheckboxType::class, [
                'label' => 'admin.product.customizable',
                'required' => false,
                'data' => true
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'admin.product.sort_order',
                'data' => 0
            ])
            ->add('availableOptions', EntityType::class, [
                'class' => ProductOption::class,
                'choice_label' => 'name',
                'label' => 'admin.product.available_options',
                'multiple' => true,
                'required' => false,
                'help' => 'admin.product.available_options_help'
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

        // Translations are handled directly in Twig template and controllers
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}