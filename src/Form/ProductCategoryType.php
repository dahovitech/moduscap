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

class ProductCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'help' => 'Identifiant unique de la catégorie',
                'attr' => ['readonly' => true]
            ])
            ->add('basePrice', MoneyType::class, [
                'label' => 'Prix de base (€)',
                'currency' => 'EUR',
                'required' => false,
                'help' => 'Prix de base de la catégorie'
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Catégorie active',
                'required' => false
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'Catégorie mise en avant',
                'required' => false
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'data' => 0
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => ProductCategoryTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Traductions'
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