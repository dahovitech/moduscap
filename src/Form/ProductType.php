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
                'label' => 'Code',
                'help' => 'Identifiant unique du produit',
                'attr' => ['readonly' => true]
            ])
            ->add('category', EntityType::class, [
                'class' => ProductCategory::class,
                'choice_label' => 'name',
                'label' => 'Catégorie'
            ])
            ->add('basePrice', MoneyType::class, [
                'label' => 'Prix de base (€)',
                'currency' => 'EUR',
                'required' => false,
                'help' => 'Prix de base du produit'
            ])
            ->add('surface', TextType::class, [
                'label' => 'Surface (m²)',
                'required' => false
            ])
            ->add('dimensions', TextType::class, [
                'label' => 'Dimensions',
                'required' => false,
                'help' => 'Ex: 6m × 4,7m × 2,8m'
            ])
            ->add('rooms', IntegerType::class, [
                'label' => 'Nombre de pièces',
                'required' => false,
                'data' => 1
            ])
            ->add('height', IntegerType::class, [
                'label' => 'Hauteur (cm)',
                'required' => false
            ])
            ->add('materials', TextareaType::class, [
                'label' => 'Matériaux',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('equipment', TextareaType::class, [
                'label' => 'Équipements',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('specifications', TextareaType::class, [
                'label' => 'Spécifications techniques',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('advantages', TextareaType::class, [
                'label' => 'Avantages',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('technicalSpecs', TextareaType::class, [
                'label' => 'Spécifications techniques détaillées',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('assemblyTime', IntegerType::class, [
                'label' => 'Temps d\'assemblage (jours)',
                'required' => false
            ])
            ->add('energyClass', ChoiceType::class, [
                'label' => 'Classe énergétique',
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
                'label' => 'Garantie structure (ans)',
                'required' => false,
                'data' => 10
            ])
            ->add('warrantyEquipment', IntegerType::class, [
                'label' => 'Garantie équipements (ans)',
                'required' => false,
                'data' => 5
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Produit actif',
                'required' => false
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'Produit mis en avant',
                'required' => false
            ])
            ->add('isCustomizable', CheckboxType::class, [
                'label' => 'Produit personnalisable',
                'required' => false,
                'data' => true
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'data' => 0
            ])
            ->add('availableOptions', EntityType::class, [
                'class' => ProductOption::class,
                'choice_label' => 'name',
                'label' => 'Options disponibles',
                'multiple' => true,
                'required' => false,
                'help' => 'Sélectionnez les options disponibles pour ce produit'
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => ProductTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Traductions'
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