<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ContactType extends AbstractType
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
                        'max' => 100,
                        'minMessage' => 'Votre prénom doit comporter au moins {{ limit }} caractères',
                        'maxMessage' => 'Votre prénom ne peut pas dépasser {{ limit }} caractères',
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
                        'max' => 100,
                        'minMessage' => 'Votre nom doit comporter au moins {{ limit }} caractères',
                        'maxMessage' => 'Votre nom ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre email',
                    ]),
                    new Length([

                        'max' => 100,

                        'maxMessage' => 'Votre email ne peut pas dépasser {{ limit }} caractères',
                    ])
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Votre sujet doit comporter au moins {{ limit }} caractères',
                        'maxMessage' => 'Votre sujet ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez entrer un sujet',
                    ])
                ]
            ])
            ->add('message', TextType::class, [
                'label' => 'Message',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un message',
                    ])
                ]
            ])
            ->add('send', SubmitType::class, [
                'label' => 'Envoyer'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
