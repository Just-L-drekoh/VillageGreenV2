<?php
namespace App\Form;


use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $availableTypes = $options['available_types'] ?? [];
        $builder
            ->add('address', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une adresse.']),
                    new Length(['min' => 5, 'max' => 255, 'minMessage' => 'L\'adresse doit contenir au moins 5 caractères.', 'maxMessage' => 'L\'adresse ne peut pas dépasser 255 caractères.']),
                ],
            ])
            ->add('city', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une ville.']),
                    new Length(['min' => 2, 'max' => 50, 'minMessage' => 'La ville doit contenir au moins 2 caractères.', 'maxMessage' => 'La ville ne peut pas dépasser 50 caractères.']),
                ],
            ])
            ->add('cp', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un code postal.']),
                    new Regex(['pattern' => '/^\d{5}$/', 'message' => 'Le code postal doit être composé de 5 chiffres.']),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => $availableTypes,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir un type d\'adresse.']),
                ],
                'label' => 'Type d\'adresse',
            ])
            ->add('complement', null, [
                'constraints' => [
                    new Length(['max' => 255, 'maxMessage' => 'Le complément d\'adresse ne peut pas dépasser 255 caractères.']),
                ],
            ])
            ->add('isDefault', null, [
                'label' => 'Adresse par défaut',
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir si l\'adresse est par défaut.']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            
        'available_types' => [],
        ]);
    }
}