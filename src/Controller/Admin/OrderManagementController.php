<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\User;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/orders')]
class OrderManagementController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private EmailService $emailService,
        private TranslatorInterface $translator
    ) {}

    /**
     * Orders dashboard
     */
    #[Route('/', name: 'admin_orders_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status');
        $search = $request->query->get('search');
        $sortBy = $request->query->get('sort_by', 'createdAt');
        $sortOrder = $request->query->get('sort_order', 'DESC');

        $qb = $this->entityManager->getRepository(Order::class)->createQueryBuilder('o');

        if ($status) {
            $qb->andWhere('o.status = :status')
               ->setParameter('status', $status);
        }

        if ($search) {
            $qb->andWhere('o.orderNumber LIKE :search OR o.clientName LIKE :search OR o.clientEmail LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('o.' . $sortBy, $sortOrder);

        $orders = $qb->getQuery()->getResult();
        
        // Get statistics
        $stats = $this->entityManager->getRepository(Order::class)->getStatistics();
        
        // Get pending payment reminders
        $reminders = $this->entityManager->getRepository(Order::class)->findOrdersNeedingReminders();

        return $this->render('admin/orders/index.html.twig', [
            'orders' => $orders,
            'stats' => $stats,
            'pending_reminders' => $reminders,
            'current_status' => $status,
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ]);
    }

    /**
     * Show order details
     */
    #[Route('/{id}', name: 'admin_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        $orderItems = $this->entityManager->getRepository(\App\Entity\OrderItem::class)->findByOrder($order);
        
        return $this->render('admin/orders/show.html.twig', [
            'order' => $order,
            'order_items' => $orderItems
        ]);
    }

    /**
     * Approve order
     */
    #[Route('/{id}/approve', name: 'admin_order_approve', methods: ['POST'])]
    public function approve(Request $request, Order $order): Response
    {
        if (!$order->canBeApproved()) {
            $this->addFlash('error', $this->translator->trans('admin.errors.order.cannot_be_approved', [], 'admin'));
            return $this->redirectToRoute('admin_orders_index');
        }

        $order->setStatus(Order::STATUS_APPROVED);
        $order->setApprovedBy($this->getUser());
        $order->setApprovedAt(new \DateTime());
        $order->setRejectionReason(null);

        $this->entityManager->flush();

        // Send approval email
        try {
            $this->emailService->sendOrderApproval($order);
        } catch (\Exception $e) {
            // Log error but don't interrupt the process
            error_log('Error sending approval email: ' . $e->getMessage());
        }

        $this->addFlash('success', $this->translator->trans('admin.errors.order.approved_successfully', [], 'admin'));
        
        return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
    }

    /**
     * Reject order
     */
    #[Route('/{id}/reject', name: 'admin_order_reject', methods: ['POST'])]
    public function reject(Request $request, Order $order): Response
    {
        if (!$order->canBeRejected()) {
            $this->addFlash('error', $this->translator->trans('admin.errors.order.cannot_be_rejected', [], 'admin'));
            return $this->redirectToRoute('admin_orders_index');
        }

        $rejectionReason = $request->request->get('rejection_reason');
        
        if (empty($rejectionReason)) {
            $this->addFlash('error', $this->translator->trans('admin.errors.order.rejection_reason_required', [], 'admin'));
            return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
        }

        $order->setStatus(Order::STATUS_REJECTED);
        $order->setRejectionReason($rejectionReason);
        $order->setApprovedBy($this->getUser());

        $this->entityManager->flush();

        // Send rejection email
        try {
            $this->emailService->sendOrderRejection($order);
        } catch (\Exception $e) {
            // Log error but don't interrupt the process
            error_log('Error sending rejection email: ' . $e->getMessage());
        }

        $this->addFlash('success', $this->translator->trans('admin.errors.order.rejected_successfully', [], 'admin'));
        
        return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
    }

    /**
     * Mark order as paid
     */
    #[Route('/{id}/mark-paid', name: 'admin_order_mark_paid', methods: ['POST'])]
    public function markAsPaid(Request $request, Order $order): Response
    {
        if (!$order->getPaymentProof()) {
            $this->addFlash('error', $this->translator->trans('admin.errors.order.no_payment_proof', [], 'admin'));
            return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
        }

        $order->setStatus(Order::STATUS_PAID);
        $order->setPaidAt(new \DateTime());

        $this->entityManager->flush();

        // Send payment confirmation email
        try {
            $this->emailService->sendPaymentConfirmation($order);
        } catch (\Exception $e) {
            // Log error but don't interrupt the process
            error_log('Error sending payment confirmation email: ' . $e->getMessage());
        }

        $this->addFlash('success', $this->translator->trans('admin.errors.order.marked_as_paid', [], 'admin'));
        
        return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
    }

    /**
     * Send payment reminder
     */
    #[Route('/{id}/send-reminder', name: 'admin_order_send_reminder', methods: ['POST'])]
    public function sendPaymentReminder(Request $request, Order $order): Response
    {
        if (!$order->canSendPaymentReminder()) {
            $this->addFlash('error', $this->translator->trans('admin.errors.order.reminder_cannot_be_sent', [], 'admin'));
            return $this->redirectToRoute('admin_orders_index');
        }

        $result = $this->sendPaymentReminderEmail($order);
        
        if ($result['success']) {
            $this->addFlash('success', $this->translator->trans('admin.errors.order.reminder_sent_successfully', ['%email%' => $order->getClientEmail()], 'admin'));
        } else {
            $this->addFlash('error', $this->translator->trans('admin.errors.order.reminder_send_error', ['%error%' => $result['error']], 'admin'));
        }

        return $this->redirectToRoute('admin_orders_index');
    }

    /**
     * Bulk actions for orders
     */
    #[Route('/bulk-action', name: 'admin_orders_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request): Response
    {
        $action = $request->request->get('action');
        $orderIds = $request->request->all('order_ids');
        
        if (empty($orderIds)) {
            $this->addFlash('error', $this->translator->trans('admin.errors.order.no_order_selected', [], 'admin'));
            return $this->redirectToRoute('admin_orders_index');
        }

        $orders = $this->entityManager->getRepository(Order::class)->findBy(['id' => $orderIds]);
        $processed = 0;
        $errors = [];

        foreach ($orders as $order) {
            try {
                switch ($action) {
                    case 'approve':
                        if ($order->canBeApproved()) {
                            $order->setStatus(Order::STATUS_APPROVED);
                            $order->setApprovedBy($this->getUser());
                            $order->setApprovedAt(new \DateTime());
                            $processed++;
                        }
                        break;
                        
                    case 'reject':
                        if ($order->canBeRejected()) {
                            $order->setStatus(Order::STATUS_REJECTED);
                            $order->setRejectionReason($this->translator->trans('admin.errors.order.bulk_rejected_reason', [], 'admin'));
                            $order->setApprovedBy($this->getUser());
                            $processed++;
                        }
                        break;
                        
                    case 'send_reminders':
                        if ($order->canSendPaymentReminder()) {
                            $this->sendPaymentReminderEmail($order);
                            $processed++;
                        }
                        break;
                }
            } catch (\Exception $e) {
                $errors[] = $this->translator->trans('admin.errors.order.processing_error', ['%number%' => $order->getOrderNumber(), '%error%' => $e->getMessage()], 'admin');
            }
        }

        if ($processed > 0) {
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('admin.errors.order.orders_processed_successfully', ['%count%' => $processed], 'admin'));
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->redirectToRoute('admin_orders_index');
    }

    /**
     * Download payment proof
     */
    #[Route('/{id}/download-payment-proof', name: 'admin_order_download_payment_proof', methods: ['GET'])]
    public function downloadPaymentProof(Order $order): Response
    {
        if (!$order->getPaymentProof()) {
            throw $this->createNotFoundException($this->translator->trans('admin.errors.order.no_payment_proof_file', [], 'admin'));
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/payment_proofs/' . $order->getPaymentProof();
        
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException($this->translator->trans('admin.errors.order.payment_proof_not_found', [], 'admin'));
        }

        return $this->file($filePath);
    }

    /**
     * Update order status
     */
    #[Route('/{id}/update-status', name: 'admin_order_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $newStatus = $request->request->get('status');
        
        if (!in_array($newStatus, [
            Order::STATUS_PENDING,
            Order::STATUS_APPROVED, 
            Order::STATUS_REJECTED,
            Order::STATUS_PAID,
            Order::STATUS_PROCESSING,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_CANCELLED
        ])) {
            return new JsonResponse(['error' => $this->translator->trans('admin.errors.order.invalid_status', [], 'admin')], 400);
        }

        $order->setStatus($newStatus);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $this->translator->trans('admin.errors.order.status_updated_successfully', [], 'admin'),
            'order' => [
                'id' => $order->getId(),
                'order_number' => $order->getOrderNumber(),
                'status' => $order->getStatus(),
                'updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    /**
     * Get order statistics
     */
    #[Route('/statistics', name: 'admin_orders_statistics', methods: ['GET'])]
    public function getStatistics(): JsonResponse
    {
        $stats = $this->entityManager->getRepository(Order::class)->getStatistics();
        
        return new JsonResponse([
            'success' => true,
            'statistics' => $stats
        ]);
    }

    /**
     * Send payment reminder email
     */
    private function sendPaymentReminderEmail(Order $order): array
    {
        try {
            if ($this->emailService->sendPaymentReminder($order)) {
                return ['success' => true, 'message' => $this->translator->trans('admin.errors.system.reminder_sent', [], 'admin')];
            } else {
                return ['success' => false, 'error' => $this->translator->trans('admin.errors.system.reminder_send_failed', [], 'admin')];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get payment information from settings
     */
    private function getPaymentInfo(): ?string
    {
        $setting = $this->entityManager->getRepository(\App\Entity\Setting::class)->findOneBy([]);
        return $setting?->getPaymentInfo();
    }

    /**
     * Export orders to CSV
     */
    #[Route('/export', name: 'admin_orders_export', methods: ['POST'])]
    public function export(Request $request): Response
    {
        $format = $request->request->get('format', 'csv');
        $status = $request->request->get('status');
        $dateFrom = $request->request->get('date_from');
        $dateTo = $request->request->get('date_to');

        $qb = $this->entityManager->getRepository(Order::class)->createQueryBuilder('o');

        if ($status) {
            $qb->andWhere('o.status = :status')
               ->setParameter('status', $status);
        }

        if ($dateFrom) {
            $dateFromObj = \DateTime::createFromFormat('Y-m-d', $dateFrom);
            if ($dateFromObj) {
                $qb->andWhere('o.createdAt >= :dateFrom')
                   ->setParameter('dateFrom', $dateFromObj);
            }
        }

        if ($dateTo) {
            $dateToObj = \DateTime::createFromFormat('Y-m-d', $dateTo);
            if ($dateToObj) {
                $dateToObj->setTime(23, 59, 59);
                $qb->andWhere('o.createdAt <= :dateTo')
                   ->setParameter('dateTo', $dateToObj);
            }
        }

        $orders = $qb->orderBy('o.createdAt', 'DESC')->getQuery()->getResult();

        if ($format === 'csv') {
            return $this->exportToCsv($orders);
        }

        $this->addFlash('error', $this->translator->trans('admin.errors.order.unsupported_export_format', [], 'admin'));
        return $this->redirectToRoute('admin_orders_index');
    }

    /**
     * Export orders to CSV format
     */
    private function exportToCsv(array $orders): Response
    {
        $csvContent = $this->translator->trans('admin.errors.order.csv_headers', [], 'admin') . "\n";
        
        foreach ($orders as $order) {
            $csvContent .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $order->getOrderNumber(),
                $order->getClientName() ?: '',
                $order->getClientEmail() ?: '',
                $order->getClientPhone() ?: '',
                $order->getStatus(),
                $order->getTotal(),
                $order->getCreatedAt()->format('Y-m-d H:i:s')
            );
        }

        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="orders_export.csv"');
        
        return $response;
    }
}