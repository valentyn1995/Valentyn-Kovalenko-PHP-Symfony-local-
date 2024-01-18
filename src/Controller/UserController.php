<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\RegisterFormType;
use App\Repository\UserRepository;
use App\Services\EmailRegisterService;
use App\Services\TokenService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailRegisterService $emailRegisterService,
        private TokenService $tokenService,
        private UserPasswordHasherInterface $passwordHasher
    ) {

    }

    #[Route('/registration', name: 'form_registration')]
    public function create(Request $request): Response
    {
        $user = new User();

        $registerForm = $this->createForm(RegisterFormType::class, $user);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
            $user->setRoles(['ROLE_USER']);
            $user->setConfirmed(false);

            $tokenValue = $this->tokenService->generateToken();
            $user->setToken($tokenValue);

            $this->userRepository->save($user);

            $userEmail = $user->getEmail();
            $this->emailRegisterService->sendEmail($userEmail, $tokenValue);

            return $this->render('registration/send_token_on_email.html.twig');
        }

        return $this->render('registration/create_user.html.twig', [
            'register_form' => $registerForm->createView(),
        ]);
    }

    #[Route('/confirmation/{token}', name: 'email_confirmation', requirements: ['token' => '\w{32}'])]
    public function confirmEmail(string $token): Response
    {
        $user = $this->userRepository->findOneBy(['token' => $token]);

        if ($user) {
            $user->setConfirmed(true);
            $this->userRepository->save($user);

            return $this->render('registration/confirmation.html.twig');
        } else {
            return $this->render('registration/token_not_found.html.twig');
        }
    }
}