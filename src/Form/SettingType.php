<?php

namespace App\Form;

use App\Entity\Setting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Media;

class SettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Informations générales du site
            ->add('siteName', TextType::class, [
                'label' => 'settings.site_name.label',
                'label_translation_parameters' => [],
                'help' => 'settings.site_name.help',
                'required' => false,
                'attr' => [
                    'placeholder' => 'settings.site_name.placeholder',
                ],
            ])
            
            // Informations de contact
            ->add('phone', TelType::class, [
                'label' => 'settings.phone.label',
                'help' => 'settings.phone.help',
                'required' => false,
                'attr' => [
                    'placeholder' => 'settings.phone.placeholder',
                ],
            ])
            
            ->add('whatsapp', TelType::class, [
                'label' => 'settings.whatsapp.label',
                'help' => 'settings.whatsapp.help',
                'required' => false,
                'attr' => [
                    'placeholder' => 'settings.whatsapp.placeholder',
                ],
            ])
            
            ->add('address', TextareaType::class, [
                'label' => 'settings.address.label',
                'help' => 'settings.address.help',
                'required' => false,
                'attr' => [
                    'placeholder' => 'settings.address.placeholder',
                    'rows' => 3,
                ],
            ])
            
            // Configuration email
            ->add('email', EmailType::class, [
                'label' => 'settings.email.label',
                'help' => 'settings.email.help',
                'required' => false,
                'attr' => [
                    'placeholder' => 'settings.email.placeholder',
                ],
            ])
            
            ->add('emailSender', EmailType::class, [
                'label' => 'settings.email_sender.label',
                'help' => 'settings.email_sender.help',
                'attr' => [
                    'placeholder' => 'settings.email_sender.placeholder',
                ],
            ])
            
            ->add('emailReceived', EmailType::class, [
                'label' => 'settings.email_received.label',
                'help' => 'settings.email_received.help',
                'required' => false,
                'attr' => [
                    'placeholder' => 'settings.email_received.placeholder',
                ],
            ])
            
            // Médias
            ->add('logo', EntityType::class, [
                'class' => Media::class,
                'choice_label' => 'title',
                'label' => 'settings.logo.label',
                'help' => 'settings.logo.help',
                'required' => false,
                'placeholder' => 'settings.logo.placeholder',
            ])
            
            ->add('logoLight', EntityType::class, [
                'class' => Media::class,
                'choice_label' => 'title',
                'label' => 'settings.logo_light.label',
                'help' => 'settings.logo_light.help',
                'required' => false,
                'placeholder' => 'settings.logo_light.placeholder',
            ])
            
            ->add('favicon', EntityType::class, [
                'class' => Media::class,
                'choice_label' => 'title',
                'label' => 'settings.favicon.label',
                'help' => 'settings.favicon.help',
                'required' => false,
                'placeholder' => 'settings.favicon.placeholder',
            ])
            
            // Informations de paiement
            ->add('paymentInfo', TextareaType::class, [
                'label' => 'settings.payment_info.label',
                'help' => 'settings.payment_info.help',
                'required' => false,
                'attr' => [
                    'placeholder' => 'settings.payment_info.placeholder',
                    'rows' => 5,
                    'class' => 'wysiwyg',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Setting::class,
            'translation_domain' => 'admin',
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}