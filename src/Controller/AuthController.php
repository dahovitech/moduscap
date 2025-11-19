<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => '[a-z]{2}'])]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private EmailService $emailService,
        private ValidatorInterface $validator,
        private TranslatorInterface $translator
    ) {}

    #[Route('/login', name: 'app_user_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Redirect if already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_user_profile', ['_locale' => $request->getLocale()]);
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@theme/auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_user_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        // Redirect if already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_user_profile', ['_locale' => $request->getLocale()]);
        }

        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setFirstName($request->request->get('first_name'));
            $user->setLastName($request->request->get('last_name'));
            $user->setEmail($request->request->get('email'));
            
            $plainPassword = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            // Validate passwords match
            if ($plainPassword !== $confirmPassword) {
                $this->addFlash('error', $this->translator->trans('auth.password_mismatch', [], 'default'));
                return $this->render('@theme/auth/register.html.twig');
            }

            // Hash password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);

            // Validate user
            $errors = $this->validator->validate($user);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->render('@theme/auth/register.html.twig');
            }

            try {
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                // Send welcome email
                $this->emailService->sendRegistrationConfirmation(
                    $user->getEmail(),
                    $user->getFullName()
                );

                $this->addFlash('success', $this->translator->trans('auth.registration_success', [], 'default'));
                return $this->redirectToRoute('app_user_login', ['_locale' => $request->getLocale()]);

            } catch (\Exception $e) {
                $this->addFlash('error', $this->translator->trans('auth.registration_error', [], 'default'));
            }
        }

        return $this->render('@theme/auth/register.html.twig');
    }

    #[Route('/profile', name: 'app_user_profile')]
    public function profile(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();

        return $this->render('@theme/auth/profile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/profile/edit', name: 'app_user_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $user->setFirstName($request->request->get('first_name'));
            $user->setLastName($request->request->get('last_name'));

            // Change password if provided
            $newPassword = $request->request->get('new_password');
            if ($newPassword) {
                $confirmPassword = $request->request->get('confirm_password');
                
                if ($newPassword !== $confirmPassword) {
                    $this->addFlash('error', $this->translator->trans('auth.password_mismatch', [], 'default'));
                    return $this->render('@theme/auth/edit_profile.html.twig', ['user' => $user]);
                }

                $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }

            $errors = $this->validator->validate($user);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            } else {
                $this->entityManager->flush();
                $this->addFlash('success', $this->translator->trans('auth.profile_updated', [], 'default'));
                return $this->redirectToRoute('app_user_profile', ['_locale' => $request->getLocale()]);
            }
        }

        return $this->render('@theme/auth/edit_profile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/logout', name: 'app_user_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
