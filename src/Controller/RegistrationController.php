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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher,
     Security $security, EntityManagerInterface $entityManager,
     JWTService $jwt, SendEmailService $mail): Response
    {

        $ref = mt_rand(10000, 99999);
        $userRepository = $entityManager->getRepository(User::class);
        $existingUser = $userRepository->findOneBy(['ref' => $ref]);
        
        if ($existingUser !== null) {
            
            $ref = mt_rand(10000, 99999);
            
            $this->addFlash('error', 'Un utilisateur avec le même ref existe déjà');
            return $this->redirectToRoute('app_register');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);
            $user->setVerified(false);
            $user->setRef("Cli:{$ref}");
            $user->setLastConnect(new \DateTimeImmutable());
            $entityManager->persist($user);
            $entityManager->flush();

            $header = [
                'alg' => 'HS256',
                'typ' => 'JWT'
            ];
            $payload = [
                'id' => $user->getId(),
            ];
            
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwt_secret'));

            $mail->send(
                'no-reply@Village-green.fr',
                $user->getEmail(),
                'Activation de votre compte sur le Site Village_Green',
                'register',
                compact('user', 'token') //['user => $user, 'token' => $token]
            );
            $this->addFlash('success', 'Vous avez reçu un email pour activer votre compte');
            
            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
    //Activation du compte par token 
    public function verifUser($token, JWTService $jwt, 
    UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        //on verifie que le token est correct(correctement formé, pas expiré)

        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwt_secret'))){

            $payload = $jwt->getPayload($token);
            
            //On cherche le user qui correspond au payload

            $user = $userRepository->find($payload['id']);

            if($user && !$user->isVerified()){

                $user->setVerified(true);
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte est maintenant activé');
                return $this->redirectToRoute('app_index');
            }
            $this->addFlash('error', 'Le token est incorrect ou expiré');
            return $this->redirectToRoute('app_register');
        }
}

//Renvoi de l'email pour l'activation du compte
public function resendVerification(
    JWTService $jwt, 
    SendEmailService $mail, 
    EntityManagerInterface $entityManager
): Response {
    $user = $this->getUser();

    if (!$user instanceof User) {
        $this->addFlash('error', "L'utilisateur n'est pas connecté ou introuvable.");
        return $this->redirectToRoute('app_login');
    }

    if ($user->isVerified()) {
        $this->addFlash('info', "Votre compte est déjà activé.");
        return $this->redirectToRoute('app_profile');
    }

    // Génération d'un nouveau token
    $header = ['typ' => 'JWT', 'alg' => 'HS256'];
    $payload = ['id' => $user->getId()];
    $token = $jwt->generate($header, $payload, $this->getParameter('app.jwt_secret'));

    // Envoi de l'e-mail
    $mail->send(
        'no-reply@Village-green.fr',
        $user->getEmail(),
        'Réactivation de votre compte sur le site Village_Green',
        'register',
        compact('user', 'token')
    );

    $this->addFlash('success', "Un nouvel e-mail de vérification a été envoyé.");
    return $this->redirectToRoute('app_profil');
}
}
