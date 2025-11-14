<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => ('admin.product.code'|trans),
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => ('admin.product.code_placeholder'|trans),
                    'pattern' => '^[A-Za-z0-9_-]+$',
                    'title' => ('admin.product.code_title'|trans)
                ],
                'help' => ('admin.product.code_help'|trans)
            ])
            ->add('category', EntityType::class, [
                'class' => ProductCategory::class,
                'choice_label' => 'name',
                'label' => ('admin.product.category'|trans)
            ])
            ->add('basePrice', MoneyType::class, [
                'label' => ('admin.product.base_price'|trans),
                'currency' => 'EUR',
                'required' => false,
                'help' => ('admin.product.base_price_help'|trans)
            ])
            ->add('surface', TextType::class, [
                'label' => ('admin.product.surface'|trans),
                'required' => false
            ])
            ->add('dimensions', TextType::class, [
                'label' => ('admin.product.dimensions'|trans),
                'required' => false,
                'help' => ('admin.product.dimensions_help'|trans)
            ])
            ->add('rooms', IntegerType::class, [
                'label' => ('admin.product.rooms'|trans),
                'required' => false,
                'data' => 1
            ])
            ->add('height', IntegerType::class, [
                'label' => ('admin.product.height'|trans),
                'required' => false
            ])
            ->add('materials', TextareaType::class, [
                'label' => ('admin.product.materials'|trans),
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('equipment', TextareaType::class, [
                'label' => ('admin.product.equipment'|trans),
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('specifications', TextareaType::class, [
                'label' => ('admin.product.specifications'|trans),
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('advantages', TextareaType::class, [
                'label' => ('admin.product.advantages'|trans),
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('technicalSpecs', TextareaType::class, [
                'label' => ('admin.product.technical_specs_detailed'|trans),
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('assemblyTime', IntegerType::class, [
                'label' => ('admin.product.assembly_time'|trans),
                'required' => false
            ])
            ->add('energyClass', ChoiceType::class, [
                'label' => ('admin.product.energy_class'|trans),
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
                'label' => ('admin.product.warranty_structure'|trans),
                'required' => false,
                'data' => 10
            ])
            ->add('warrantyEquipment', IntegerType::class, [
                'label' => ('admin.product.warranty_equipment'|trans),
                'required' => false,
                'data' => 5
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => ('admin.product.active_product'|trans),
                'required' => false
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => ('admin.product.featured'|trans),
                'required' => false
            ])
            ->add('isCustomizable', CheckboxType::class, [
                'label' => ('admin.product.customizable'|trans),
                'required' => false,
                'data' => true
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => ('admin.product.sort_order'|trans),
                'data' => 0
            ])
            ->add('availableOptions', EntityType::class, [
                'class' => ProductOption::class,
                'choice_label' => 'name',
                'label' => ('admin.product.available_options'|trans),
                'multiple' => true,
                'required' => false,
                'help' => ('admin.product.available_options_help'|trans)
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => ProductTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
                'label' => ('admin.product.translations'|trans),
                'attr' => [
                    'class' => 'translations-container'
                ]
            ])
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
                'label' => ('admin.common.save'|trans),
                'attr' => [
                    'class' => 'btn btn-primary btn-lg me-2'
                ]
            ])
            ->add('save_and_continue', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
                'label' => ('admin.common.save_and_continue'|trans),
                'attr' => [
                    'class' => 'btn btn-secondary'
                ]
            ]);

        // Auto-generate code based on category and name in French
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (isset($data['category']) && isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    if (isset($translation['language']) && $translation['language'] === 'fr') {
                        $name = $translation['name'] ?? '';
                        $categoryCode = $data['category'] ?? '';
                        $code = strtolower(str_replace([' ', '-', '_'], '-', $categoryCode . '-' . $name));
                        $data['code'] = $code;
                        break;
                    }
                }
            }
            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}