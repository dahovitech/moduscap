<?php

namespace App\Form;

use App\Entity\ProductOption;
use App\Entity\ProductOptionGroup;
use App\Entity\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'help' => 'Identifiant unique de l\'option',
                'attr' => ['readonly' => true]
            ])
            ->add('optionGroup', EntityType::class, [
                'class' => ProductOptionGroup::class,
                'choice_label' => 'name',
                'label' => 'Groupe d\'options'
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix (€)',
                'currency' => 'EUR',
                'data' => 0
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Option active',
                'required' => false
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'data' => 0
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => ProductOptionTranslationType::class,
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
                        $groupCode = $data['optionGroup'] ?? '';
                        $code = strtolower(str_replace([' ', '-', '_'], '-', $groupCode . '-' . $name));
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
            'data_class' => ProductOption::class,
        ]);
    }
}