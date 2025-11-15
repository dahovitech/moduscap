<?php

namespace App\Form;

use App\Entity\ProductOptionGroup;
use App\Entity\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'help' => 'Identifiant unique du groupe',
                'attr' => ['readonly' => true]
            ])
            ->add('inputType', ChoiceType::class, [
                'label' => 'Type d\'input',
                'choices' => [
                    'Sélection' => 'select',
                    'Bouton radio' => 'radio',
                    'Case à cocher' => 'checkbox',
                    'Sélection multiple' => 'multiselect'
                ],
                'data' => 'select'
            ])
            ->add('isRequired', CheckboxType::class, [
                'label' => 'Champ requis',
                'required' => false
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Groupe actif',
                'required' => false
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'data' => 0
            ])
           ;

     
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOptionGroup::class,
        ]);
    }
}