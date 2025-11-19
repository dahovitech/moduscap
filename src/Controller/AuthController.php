<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;
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
        private TranslatorInterface $translator,
        private OrderRepository $orderRepository
    ) {}

    #[Route('/login', name: 'app_user_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Redirect if already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_user_dashboard', ['_locale' => $request->getLocale()]);
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
            return $this->redirectToRoute('app_user_dashboard', ['_locale' => $request->getLocale()]);
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

    #[Route('/dashboard', name: 'app_user_dashboard')]
    public function dashboard(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        // Get user's orders by email
        $orders = $this->orderRepository->findBy(
            ['clientEmail' => $user->getEmail()],
            ['createdAt' => 'DESC']
        );

        // Calculate statistics
        $totalOrders = count($orders);
        $pendingOrders = count(array_filter($orders, fn($o) => in_array($o->getStatus(), ['pending', 'approved'])));
        $completedOrders = count(array_filter($orders, fn($o) => $o->getStatus() === 'delivered'));
        $totalSpent = array_reduce($orders, fn($sum, $o) => $sum + ($o->getStatus() === 'delivered' ? $o->getTotal() : 0), 0);

        // Get recent orders (last 5)
        $recentOrders = array_slice($orders, 0, 5);

        // Get orders awaiting payment
        $awaitingPayment = array_filter($orders, fn($o) => $o->getStatus() === 'approved' && !$o->getPaymentProof());

        return $this->render('@theme/auth/dashboard.html.twig', [
            'user' => $user,
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'completedOrders' => $completedOrders,
            'totalSpent' => $totalSpent,
            'recentOrders' => $recentOrders,
            'awaitingPayment' => $awaitingPayment,
        ]);
    }

    #[Route('/my-orders', name: 'app_user_orders')]
    public function myOrders(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        // Get filter parameters
        $status = $request->query->get('status');
        $search = $request->query->get('search');

        // Build query
        $queryBuilder = $this->orderRepository->createQueryBuilder('o')
            ->where('o.clientEmail = :email')
            ->setParameter('email', $user->getEmail())
            ->orderBy('o.createdAt', 'DESC');

        if ($status) {
            $queryBuilder->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        if ($search) {
            $queryBuilder->andWhere('o.orderNumber LIKE :search OR o.clientName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $orders = $queryBuilder->getQuery()->getResult();

        return $this->render('@theme/auth/orders/index.html.twig', [
            'orders' => $orders,
            'currentStatus' => $status,
            'searchTerm' => $search,
        ]);
    }

    #[Route('/my-orders/{orderNumber}', name: 'app_user_order_detail')]
    public function orderDetail(string $orderNumber): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        $order = $this->orderRepository->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order) {
            throw $this->createNotFoundException($this->translator->trans('client.order.not_found', [], 'default'));
        }

        // Security: Check if order belongs to this user
        if ($order->getClientEmail() !== $user->getEmail()) {
            throw $this->createAccessDeniedException($this->translator->trans('client.order.access_denied', [], 'default'));
        }

        return $this->render('@theme/auth/orders/show.html.twig', [
            'order' => $order,
            'order_items' => $order->getOrderItems(),
        ]);
    }

    #[Route('/my-orders/{orderNumber}/track', name: 'app_user_order_track')]
    public function trackOrder(string $orderNumber): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        $order = $this->orderRepository->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order) {
            throw $this->createNotFoundException($this->translator->trans('client.order.not_found', [], 'default'));
        }

        // Security: Check if order belongs to this user
        if ($order->getClientEmail() !== $user->getEmail()) {
            throw $this->createAccessDeniedException($this->translator->trans('client.order.access_denied', [], 'default'));
        }

        // Build status timeline
        $timeline = [
            ['status' => 'pending', 'date' => $order->getCreatedAt(), 'completed' => true],
            ['status' => 'approved', 'date' => $order->getApprovedAt(), 'completed' => $order->getApprovedAt() !== null],
            ['status' => 'paid', 'date' => $order->getPaidAt(), 'completed' => $order->getPaidAt() !== null],
            ['status' => 'processing', 'date' => null, 'completed' => in_array($order->getStatus(), ['processing', 'shipped', 'delivered'])],
            ['status' => 'shipped', 'date' => null, 'completed' => in_array($order->getStatus(), ['shipped', 'delivered'])],
            ['status' => 'delivered', 'date' => null, 'completed' => $order->getStatus() === 'delivered'],
        ];

        return $this->render('@theme/auth/orders/track.html.twig', [
            'order' => $order,
            'timeline' => $timeline,
        ]);
    }

    #[Route('/my-orders/{orderNumber}/cancel', name: 'app_user_order_cancel', methods: ['POST'])]
    public function cancelOrder(Request $request, string $orderNumber): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        $order = $this->orderRepository->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order) {
            throw $this->createNotFoundException($this->translator->trans('client.order.not_found', [], 'default'));
        }

        // Security: Check if order belongs to this user
        if ($order->getClientEmail() !== $user->getEmail()) {
            throw $this->createAccessDeniedException($this->translator->trans('client.order.access_denied', [], 'default'));
        }

        // Only pending orders can be cancelled by client
        if ($order->getStatus() !== 'pending') {
            $this->addFlash('error', $this->translator->trans('client.order.cannot_cancel', [], 'default'));
            return $this->redirectToRoute('app_user_order_detail', [
                '_locale' => $request->getLocale(),
                'orderNumber' => $orderNumber
            ]);
        }

        $order->setStatus('cancelled');
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('client.order.cancelled_success', [], 'default'));
        
        return $this->redirectToRoute('app_user_orders', ['_locale' => $request->getLocale()]);
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
