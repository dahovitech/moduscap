<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/users', name: 'admin_user_')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private TranslatorInterface $translator
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'include_password' => true,
            'password_required' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('admin.errors.user.created_success', [], 'admin'));

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user, [
            'include_password' => true,
            'password_required' => false
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('admin.errors.user.updated_success', [], 'admin'));

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle-active', name: 'toggle_active', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleActive(User $user): Response
    {
        $user->setIsActive(!$user->isActive());
        $this->entityManager->flush();

        $status = $user->isActive()
            ? $this->translator->trans('admin.errors.user.status.activated', [], 'admin')
            : $this->translator->trans('admin.errors.user.status.deactivated', [], 'admin');

        $this->addFlash('success', $this->translator->trans('admin.errors.user.toggled_success', ['%status%' => $status], 'admin'));

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, User $user): Response
    {
        if ($user === $this->getUser()) {
            $this->addFlash('error', $this->translator->trans('admin.errors.user.cannot_delete_self', [], 'admin'));
            return $this->redirectToRoute('admin_user_index');
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('admin.errors.user.deleted_success', [], 'admin'));
        } else {
            $this->addFlash('error', $this->translator->trans('admin.errors.user.invalid_csrf_token', [], 'admin'));
        }

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/profile', name: 'profile', methods: ['GET', 'POST'])]
    public function profile(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user, [
            'include_password' => true,
            'password_required' => false
        ]);

        $form->remove('roles');
        $form->remove('isActive');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('admin.errors.user.profile_updated_success', [], 'admin'));

            return $this->redirectToRoute('admin_user_profile');
        }

        return $this->render('admin/user/profile.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}