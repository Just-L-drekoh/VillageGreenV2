<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BankCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre Nom :',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est requis']),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Le nom ne peut pas dépasser 255 caractères',
                    ]),
                ],
            ])
            ->add('number', TextType::class, [
                'label' => 'Votre Numero de Carte :',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro de carte est requis']),
                    new Assert\Regex([
                        'pattern' => '/^\d{16}$/',
                        'message' => 'Le numéro de carte doit contenir exactement 16 chiffres',
                    ]),
                ],
            ])
            ->add('cvv', TextType::class, [
                'label' => 'Votre CVV :',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le CVV est requis']),
                    new Assert\Regex([
                        'pattern' => '/^\d{3}$/',
                        'message' => 'Le CVV doit contenir exactement 3 chiffres',
                    ]),
                ],
            ])
            ->add('date', TextType::class, [
                'label' => 'Votre Date de Carte :',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date est requise']),
                    new Assert\Regex([
                        'pattern' => '/^\d{2}\/\d{4}$/',
                        'message' => 'La date doit être au format JJ/MM/AAAA',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'MM/AAAA',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Sauvegarder la carte .',
            ]);
    }



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'payment_form_token',
        ]);
    }
}
