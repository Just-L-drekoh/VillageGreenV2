<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'paiement',
                ChoiceType::class,

                [
                    'mapped' => false,
                    'choices' => [
                        'carte bancaire' => 'carte bancaire',
                        'cheque' => 'cheque',
                        'virement bancaire' => 'virement bancaire',
                    ]
                ]
            )
            ->add('submit', SubmitType::class, [
                'label' => 'Valider le moyen de paiement',
                'attr' => [
                    'autofocus' => true,
                    'autofocus-submit' => true,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
