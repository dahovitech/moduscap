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
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => $this->translator->trans('admin.product.code'),
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => $this->translator->trans('admin.product.code_placeholder'),
                    'pattern' => '^[A-Za-z0-9_-]+$',
                    'title' => $this->translator->trans('admin.product.code_title')
                ],
                'help' => $this->translator->trans('admin.product.code_help')
            ])
            ->add('category', EntityType::class, [
                'class' => ProductCategory::class,
                'choice_label' => 'name',
                'label' => $this->translator->trans('admin.product.category')
            ])
            ->add('basePrice', MoneyType::class, [
                'label' => $this->translator->trans('admin.product.base_price'),
                'currency' => 'EUR',
                'required' => false,
                'help' => $this->translator->trans('admin.product.base_price_help')
            ])
            ->add('surface', TextType::class, [
                'label' => $this->translator->trans('admin.product.surface'),
                'required' => false
            ])
            ->add('dimensions', TextType::class, [
                'label' => $this->translator->trans('admin.product.dimensions'),
                'required' => false,
                'help' => $this->translator->trans('admin.product.dimensions_help')
            ])
            ->add('rooms', IntegerType::class, [
                'label' => $this->translator->trans('admin.product.rooms'),
                'required' => false,
                'data' => 1
            ])
            ->add('height', IntegerType::class, [
                'label' => $this->translator->trans('admin.product.height'),
                'required' => false
            ])
            ->add('materials', TextareaType::class, [
                'label' => $this->translator->trans('admin.product.materials'),
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('equipment', TextareaType::class, [
                'label' => $this->translator->trans('admin.product.equipment'),
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('specifications', TextareaType::class, [
                'label' => $this->translator->trans('admin.product.specifications'),
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('advantages', TextareaType::class, [
                'label' => $this->translator->trans('admin.product.advantages'),
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('technicalSpecs', TextareaType::class, [
                'label' => $this->translator->trans('admin.product.technical_specs_detailed'),
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('assemblyTime', IntegerType::class, [
                'label' => $this->translator->trans('admin.product.assembly_time'),
                'required' => false
            ])
            ->add('energyClass', ChoiceType::class, [
                'label' => $this->translator->trans('admin.product.energy_class'),
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
                'label' => $this->translator->trans('admin.product.warranty_structure'),
                'required' => false,
                'data' => 10
            ])
            ->add('warrantyEquipment', IntegerType::class, [
                'label' => $this->translator->trans('admin.product.warranty_equipment'),
                'required' => false,
                'data' => 5
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => $this->translator->trans('admin.product.active_product'),
                'required' => false
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => $this->translator->trans('admin.product.featured'),
                'required' => false
            ])
            ->add('isCustomizable', CheckboxType::class, [
                'label' => $this->translator->trans('admin.product.customizable'),
                'required' => false,
                'data' => true
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => $this->translator->trans('admin.product.sort_order'),
                'data' => 0
            ])
            ->add('availableOptions', EntityType::class, [
                'class' => ProductOption::class,
                'choice_label' => 'name',
                'label' => $this->translator->trans('admin.product.available_options'),
                'multiple' => true,
                'required' => false,
                'help' => $this->translator->trans('admin.product.available_options_help')
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => ProductTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
                'label' => $this->translator->trans('admin.product.translations'),
                'attr' => [
                    'class' => 'translations-container'
                ]
            ])
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
                'label' => $this->translator->trans('admin.common.save'),
                'attr' => [
                    'class' => 'btn btn-primary btn-lg me-2'
                ]
            ])
            ->add('save_and_continue', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
                'label' => $this->translator->trans('admin.common.save_and_continue'),
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