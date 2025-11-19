<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Setting;
use App\Entity\OrderItem;
use App\Service\EmailService;
use App\Service\PriceCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}/quote', requirements: ['_locale' => '[a-z]{2}'])]
class QuoteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PriceCalculatorService $priceCalculator,
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
        private EmailService $emailService
    ) {}

    /**
     * Create quote from product customization
     */
    #[Route('/create', name: 'app_quote_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        $customization = $request->getSession()->get('product_customization');
        
        if (!$customization) {
            $this->addFlash('error', $this->translator->trans('controller.quote.no_customization_found'));
            return $this->redirectToRoute('app_products_index');
        }

        $product = $this->entityManager->getRepository(Product::class)->find($customization['product_id']);
        
        if (!$product || !$product->isActive()) {
            $this->addFlash('error', $this->translator->trans('controller.product.not_found'));
            return $this->redirectToRoute('app_products_index');
        }

        // Calculate final pricing
        $pricing = $this->priceCalculator->calculateOrderItemPrice(
            $product,
            $customization['selected_options'],
            $customization['quantity']
        );

        if ($request->isMethod('POST')) {
            return $this->handleQuoteSubmission($request, $product, $customization, $pricing);
        }

        // Get payment info
        $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
        $paymentInfo = $setting?->getPaymentInfo();

        return $this->render('@theme/quote/create.html.twig', [
            'product' => $product,
            'customization' => $customization,
            'pricing' => $pricing,
            'payment_info' => $paymentInfo,
            'breakdown' => $this->priceCalculator->getPricingBreakdown(
                $product,
                $customization['selected_options'],
                $customization['quantity']
            )
        ]);
    }

    /**
     * Submit quote and create order
     */
    private function handleQuoteSubmission(Request $request, Product $product, array $customization, array $pricing): Response
    {
        $order = new Order();
        
        // Associate user to order (MANDATORY)
        $user = $this->getUser();
        $order->setUser($user);
        
        // Set client information from customization or user profile
        $clientInfo = $customization['client_info'];
        $order->setClientName($clientInfo['name'] ?? ($user->getFirstName() . ' ' . $user->getLastName()));
        $order->setClientEmail($clientInfo['email'] ?? $user->getEmail());
        $order->setClientPhone($clientInfo['phone'] ?? '');
        $order->setClientAddress($clientInfo['address'] ?? '');
        $order->setClientNotes($customization['customization_notes']);

        // Create order item
        $orderItem = new OrderItem();
        $orderItem->setProduct($product);
        $orderItem->setQuantity($customization['quantity']);
        $orderItem->setUnitPrice($pricing['unit_price']);
        $orderItem->setOptionsPrice($pricing['options_price']);
        $orderItem->setTotalPrice($pricing['total']);
        $orderItem->setSelectedOptions($customization['selected_options']);
        $orderItem->setCustomizationNotes($customization['customization_notes']);

        // Add option details to selected options for order tracking
        $selectedOptionsData = [];
        foreach ($customization['selected_options'] as $optionCode) {
            $option = $this->entityManager->getRepository(\App\Entity\ProductOption::class)->findOneBy(['code' => $optionCode]);
            if ($option) {
                $selectedOptionsData[] = [
                    'id' => $option->getId(),
                    'code' => $option->getCode(),
                    'name' => $option->getName(),
                    'price' => $option->getPrice()
                ];
            }
        }
        $orderItem->setSelectedOptions($selectedOptionsData);

        $order->addOrderItem($orderItem);
        $order->setSubtotal($pricing['subtotal']);
        $order->setTotal($pricing['total']);

        // Validate order
        $errors = $this->validator->validate($order);
        
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            return $this->redirectToRoute('app_quote_create');
        }

        // Save order
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // Clear customization from session
        $request->getSession()->remove('product_customization');

        $this->addFlash('success', $this->translator->trans('controller.quote.created_successfully', ['%number%' => $order->getOrderNumber()]));
        
        return $this->redirectToRoute('app_quote_confirmation', [
            'order_number' => $order->getOrderNumber()
        ]);
    }

    /**
     * Quote confirmation page
     */
    #[Route('/confirmation/{order_number}', name: 'app_quote_confirmation', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function confirmation(Request $request, string $orderNumber): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['orderNumber' => $orderNumber]);
        
        if (!$order) {
            throw $this->createNotFoundException($this->translator->trans('controller.order.not_found'));
        }

        // Security: Check if order belongs to this user
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot access this order.');
        }

        $paymentInfo = null;
        $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
        if ($setting) {
            $paymentInfo = $setting->getPaymentInfo();
        }

        return $this->render('@theme/quote/confirmation.html.twig', [
            'order' => $order,
            'payment_info' => $paymentInfo
        ]);
    }

    /**
     * Upload payment proof
     */
    #[Route('/{order_number}/upload-payment', name: 'app_quote_upload_payment', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadPaymentProof(Request $request, string $orderNumber): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['orderNumber' => $orderNumber]);
        
        if (!$order) {
            throw $this->createNotFoundException($this->translator->trans('controller.order.not_found'));
        }

        // Security: Check if order belongs to this user
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot access this order.');
        }

        /** @var UploadedFile $paymentFile */
        $paymentFile = $request->files->get('payment_proof');
        
        if (!$paymentFile) {
            $this->addFlash('error', $this->translator->trans('controller.quote.no_payment_file'));
            return $this->redirectToRoute('app_quote_confirmation', ['order_number' => $orderNumber]);
        }

        // Validate file
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($paymentFile->getMimeType(), $allowedMimeTypes)) {
            $this->addFlash('error', $this->translator->trans('controller.quote.invalid_file_type'));
            return $this->redirectToRoute('app_quote_confirmation', ['order_number' => $orderNumber]);
        }

        if ($paymentFile->getSize() > 5 * 1024 * 1024) { // 5MB limit
            $this->addFlash('error', $this->translator->trans('controller.quote.file_too_large'));
            return $this->redirectToRoute('app_quote_confirmation', ['order_number' => $orderNumber]);
        }

        try {
            // Generate unique filename
            $extension = $paymentFile->getClientOriginalExtension();
            $filename = 'payment_proof_' . $orderNumber . '_' . time() . '.' . $extension;
            
            // Move file to uploads directory
            $targetPath = $this->getParameter('kernel.project_dir') . '/public/uploads/payment_proofs/';
            
            // Create directory if it doesn't exist
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0755, true);
            }
            
            $paymentFile->move($targetPath, $filename);
            
            // Update order with payment proof filename
            $order->setPaymentProof($filename);
            $this->entityManager->flush();
            
            // Send notification to admin
            try {
                $this->emailService->sendPaymentProofUploadNotification($order);
            } catch (\Exception $e) {
                // Log error but don't interrupt the process
                error_log('Error sending payment proof notification: ' . $e->getMessage());
            }
            
            $this->addFlash('success', $this->translator->trans('controller.quote.payment_proof_uploaded_successfully'));
            
        } catch (\Exception $e) {
            $this->addFlash('error', $this->translator->trans('controller.quote.upload_error', ['%error%' => $e->getMessage()]));
        }

        return $this->redirectToRoute('app_quote_confirmation', ['order_number' => $orderNumber]);
    }

    /**
     * Check order status
     */
    #[Route('/{order_number}/status', name: 'app_quote_status', methods: ['GET'])]
    public function getOrderStatus(Request $request, string $orderNumber): JsonResponse
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['orderNumber' => $orderNumber]);
        
        if (!$order) {
            return new JsonResponse(['error' => $this->translator->trans('controller.order.not_found')], 404);
        }

        return new JsonResponse([
            'success' => true,
            'order' => [
                'order_number' => $order->getOrderNumber(),
                'status' => $order->getStatus(),
                'total' => $order->getTotal(),
                'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
                'approved_at' => $order->getApprovedAt()?->format('Y-m-d H:i:s'),
                'rejection_reason' => $order->getRejectionReason(),
                'has_payment_proof' => !empty($order->getPaymentProof())
            ]
        ]);
    }

    /**
     * Track order progress
     */
    #[Route('/{order_number}/track', name: 'app_quote_track', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function trackOrder(Request $request, string $orderNumber): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['orderNumber' => $orderNumber]);
        
        if (!$order) {
            throw $this->createNotFoundException($this->translator->trans('controller.order.not_found'));
        }

        // Security: Check if order belongs to this user
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot access this order.');
        }

        // Define status progression
        $statusSteps = [
            Order::STATUS_PENDING => [
                'label' => $this->translator->trans('controller.quote.status_steps.pending.label'),
                'description' => $this->translator->trans('controller.quote.status_steps.pending.description')
            ],
            Order::STATUS_APPROVED => [
                'label' => $this->translator->trans('controller.quote.status_steps.approved.label'),
                'description' => $this->translator->trans('controller.quote.status_steps.approved.description')
            ],
            Order::STATUS_PAID => [
                'label' => $this->translator->trans('controller.quote.status_steps.paid.label'),
                'description' => $this->translator->trans('controller.quote.status_steps.paid.description')
            ],
            Order::STATUS_PROCESSING => [
                'label' => $this->translator->trans('controller.quote.status_steps.processing.label'),
                'description' => $this->translator->trans('controller.quote.status_steps.processing.description')
            ],
            Order::STATUS_SHIPPED => [
                'label' => $this->translator->trans('controller.quote.status_steps.shipped.label'),
                'description' => $this->translator->trans('controller.quote.status_steps.shipped.description')
            ],
            Order::STATUS_DELIVERED => [
                'label' => $this->translator->trans('controller.quote.status_steps.delivered.label'),
                'description' => $this->translator->trans('controller.quote.status_steps.delivered.description')
            ]
        ];

        $currentStep = $statusSteps[$order->getStatus()] ?? null;
        $completedSteps = [];
        $pendingSteps = [];

        foreach ($statusSteps as $status => $stepInfo) {
            if ($status === $order->getStatus()) {
                $currentStep = $stepInfo;
                break;
            } elseif (in_array($status, [
                Order::STATUS_PENDING, Order::STATUS_APPROVED, Order::STATUS_PAID, 
                Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED
            ])) {
                $completedSteps[] = $stepInfo;
            } else {
                $pendingSteps[] = $stepInfo;
            }
        }

        return $this->render('@theme/quote/track.html.twig', [
            'order' => $order,
            'completed_steps' => $completedSteps,
            'current_step' => $currentStep,
            'pending_steps' => $pendingSteps,
            'status_steps' => $statusSteps
        ]);
    }
}