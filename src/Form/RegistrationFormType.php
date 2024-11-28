<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('firstName', TextType::class, [
            'label' => 'Prénom',
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez entrer votre prénom',
                ]),
                new Length([
                    'min' => 2,
                    'max' => 50,
                    'minMessage' => 'Votre prénom doit comporter au moins {{ limit }} caractères',
                    'maxMessage' => 'Votre prénom ne peut pas dépasser {{ limit }} caractères',
                ]),
            ],
        ])
        ->add('lastName', TextType::class, [
            'label' => 'Nom',
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez entrer votre nom',
                ]),
                new Length([
                    'min' => 2,
                    'max' => 50,
                    'minMessage' => 'Votre nom doit comporter au moins {{ limit }} caractères',
                    'maxMessage' => 'Votre nom ne peut pas dépasser {{ limit }} caractères',
                ]),
            ],
        ])
        ->add('phone', TextType::class, [
            'label' => 'Numéro de téléphone',
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez entrer votre numéro de téléphone',
                ]),
                new Regex([
                    'pattern' => '/^[0-9]{10}$/',
                    'message' => 'Votre numéro de téléphone doit comporter 10 chiffres',
                ]),
            ],
        ])
        ->add('email', EmailType::class, [
            'label' => 'Adresse email',
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez entrer votre adresse email',
                ]),
                new Email([
                    'message' => 'Votre adresse email doit être valide',
                ]),
            ],
        ])
        ->add('agreeTerms', CheckboxType::class, [
            'label' => 'Accepter les conditions d\'utilisation',
            'mapped' => false,
            'constraints' => [
                new IsTrue([
                    'message' => 'Vous devez accepter les conditions d\'utilisation.',
                ]),
            ],
        ])
        ->add('plainPassword', PasswordType::class, [
            'label' => 'Mot de passe',
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Entrez votre mot de passe',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                    'max' => 4096,
                ]),
                new Regex([
                    'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/',
                    'message' => 'Votre mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial',
                ]),
            ],
        ])
    ;
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
