<?php

namespace App\Form;

use App\Entity\ProductCategory;
use App\Entity\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductCategoryType extends AbstractType
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
                'label' => $this->translator->trans('admin.category.code'),
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => $this->translator->trans('admin.category.code_placeholder'),
                    'pattern' => '^[A-Za-z0-9_-]+$',
                    'title' => $this->translator->trans('admin.category.code_title')
                ],
                'help' => $this->translator->trans('admin.category.code_help')
            ])
            ->add('basePrice', MoneyType::class, [
                'label' => $this->translator->trans('admin.category.base_price'),
                'currency' => 'EUR',
                'required' => false,
                'help' => $this->translator->trans('admin.category.base_price_help'),
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => $this->translator->trans('admin.category.price_placeholder')
                ]
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => $this->translator->trans('admin.category.active_category'),
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label']
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => $this->translator->trans('admin.category.featured_category'),
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label']
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => $this->translator->trans('admin.category.sort_order'),
                'data' => 0,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => $this->translator->trans('admin.category.sort_order_placeholder')
                ],
                'help' => $this->translator->trans('admin.category.sort_order_help')
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => ProductCategoryTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
                'label' => $this->translator->trans('admin.category.translations'),
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

        // Auto-generate code based on name in French
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    if (isset($translation['language']) && $translation['language'] === 'fr') {
                        $name = $translation['name'] ?? '';
                        $code = strtolower(str_replace([' ', '-', '_'], '-', $name));
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
            'data_class' => ProductCategory::class,
        ]);
    }
}