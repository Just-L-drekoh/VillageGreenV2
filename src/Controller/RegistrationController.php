<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Service\SendEmailService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class RegistrationController extends AbstractController
{

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        JWTService $jwtService,
        SendEmailService $sendEmailService
    ): Response {

        try {
            $ref = $this->generateUniqueReference($entityManager);

            $user = new User();
            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $plainPassword = $form->get('plainPassword')->getData();
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                $user->setRoles(['ROLE_USER']);
                $user->setVerified(false);
                $user->setRef("Cli:{$ref}");
                $user->setLastConnect(new \DateTimeImmutable());

                $entityManager->persist($user);
                $entityManager->flush();

                $token = $this->generateJWT($jwtService, $user);

                $sendEmailService->send(
                    'no-reply@Village-green.fr',
                    $user->getEmail(),
                    'Activation de votre compte sur le Site Village_Green',
                    'register',
                    ['user' => $user, 'token' => $token]
                );

                $this->addFlash('success', 'Vous avez reçu un email pour activer votre compte');

                return $security->login($user, 'form_login', 'main');
            }

            return $this->render('security/register.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors de la création de votre compte.');
            return $this->redirectToRoute('villageGreen_index');
        }
    }


    #[Route('/verify-email/{token}', name: 'app_verify_email')]
    public function verifyEmail(
        string $token,
        JWTService $jwtService,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($jwtService->isValid($token) && !$jwtService->isExpired($token) && $jwtService->check($token, $this->getParameter('app.jwt_secret'))) {
            $payload = $jwtService->getPayload($token);

            $user = $userRepository->find($payload['id']);

            if ($user && !$user->isVerified()) {
                $user->setVerified(true);
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte est maintenant activé');
                return $this->redirectToRoute('VillageGreen_index');
            }

            $this->addFlash('error', 'Le token est incorrect ou expiré');
        } else {
            $this->addFlash('error', 'Le token est invalide ou a expiré');
        }

        return $this->redirectToRoute('app_register');
    }


    #[Route('/resend-email', name: 'app_resend_email')]
    public function resendVerificationEmail(
        JWTService $jwtService,
        SendEmailService $sendEmailService,

    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            $this->addFlash('error', "L'utilisateur n'est pas connecté ou introuvable.");
            return $this->redirectToRoute('app_login');
        }

        if ($user->isVerified()) {
            $this->addFlash('info', "Votre compte est déjà activé.");
            return $this->redirectToRoute('profile_index');
        }

        $token = $this->generateJWT($jwtService, $user);

        $sendEmailService->send(
            'no-reply@Village-green.fr',
            $user->getEmail(),
            'Réactivation de votre compte sur le site Village_Green',
            'register',
            ['user' => $user, 'token' => $token]
        );

        $this->addFlash('success', "Un nouvel e-mail de vérification a été envoyé.");
        return $this->redirectToRoute('profile_index');
    }


    private function generateUniqueReference(EntityManagerInterface $entityManager): string
    {
        do {
            $ref = mt_rand(10000, 99999);
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['ref' => $ref]);
        } while ($existingUser !== null);

        return $ref;
    }


    private function generateJWT(JWTService $jwtService, User $user): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = ['id' => $user->getId()];

        return $jwtService->generate($header, $payload, $this->getParameter('app.jwt_secret'));
    }
}
