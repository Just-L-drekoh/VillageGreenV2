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
    private function getPaiementOptions(?User $user): array
    {
        // Vérifier si l'utilisateur est nul
        if ($user === null || $user->getSiret() === null) {
            return [
                'Carte bancaire' => 'carte bancaire',
            ];
        }

        return [
            'Carte bancaire' => 'carte bancaire',
            'Chèque' => 'cheque',
            'Virement bancaire' => 'virement bancaire',
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $user = $options['user'] ?? null;

        $builder
            ->add(
                'paiement',
                ChoiceType::class,
                [
                    'mapped' => false,
                    'choices' => $this->getPaiementOptions($user),
                ]
            )
            ->add('submit', SubmitType::class, [
                'label' => 'Valider le moyen de paiement',
                'attr' => [
                    'autofocus' => true,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'user' => null, // Option personnalisée pour passer l'utilisateur
        ]);
    }
}
