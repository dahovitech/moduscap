<?php

namespace App\Service;

use App\Entity\ContactMessage;
use App\Entity\Order;
use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Twig\Environment;

class EmailService
{
    private ?Setting $cachedSetting = null;

    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Get site settings (cached during request)
     */
    private function getSettings(): ?Setting
    {
        if ($this->cachedSetting === null) {
            $this->cachedSetting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
        }
        
        return $this->cachedSetting;
    }

    /**
     * Get site name from settings
     */
    private function getSiteName(): string
    {
        $setting = $this->getSettings();
        return $setting?->getSiteName() ?: 'Site';
    }

    /**
     * Get sender email from settings
     */
    private function getSenderEmail(): string
    {
        $setting = $this->getSettings();
        return $setting?->getEmailSender() ?: 'noreply@example.com';
    }

    /**
     * Get receiver email from settings (for admin notifications)
     */
    private function getReceiverEmail(): string
    {
        $setting = $this->getSettings();
        return $setting?->getEmailReceived() ?: $setting?->getEmail() ?: 'admin@example.com';
    }

    /**
     * Get contact email from settings
     */
    private function getContactEmail(): string
    {
        $setting = $this->getSettings();
        return $setting?->getEmail() ?: 'contact@example.com';
    }

    /**
     * Send payment reminder email
     */
    public function sendPaymentReminder(Order $order): bool
    {
        try {
            $setting = $this->getSettings();
            
            if (!$setting || !$order->getClientEmail()) {
                return false;
            }

            $emailData = [
                'order' => $order,
                'client_name' => $order->getClientName(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'payment_info' => $setting->getPaymentInfo(),
                'site_name' => $this->getSiteName(),
                'site_email' => $this->getSenderEmail()
            ];

            $email = (new Email())
                ->from(new Address($this->getSenderEmail(), $this->getSiteName()))
                ->to($order->getClientEmail())
                ->subject('Rappel de paiement - Commande ' . $order->getOrderNumber())
                ->html($this->twig->render('emails/payment_reminder.html.twig', $emailData));

            $this->mailer->send($email);
            
            return true;
        } catch (\Exception $e) {
            // Log error in production
            error_log('Error sending payment reminder: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order approval notification
     */
    public function sendOrderApproval(Order $order): bool
    {
        try {
            $setting = $this->getSettings();
            
            if (!$setting || !$order->getClientEmail()) {
                return false;
            }

            $emailData = [
                'order' => $order,
                'client_name' => $order->getClientName(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'payment_info' => $setting->getPaymentInfo(),
                'site_name' => $this->getSiteName(),
                'site_email' => $this->getSenderEmail()
            ];

            $email = (new Email())
                ->from(new Address($this->getSenderEmail(), $this->getSiteName()))
                ->to($order->getClientEmail())
                ->subject('Commande approuvée - ' . $order->getOrderNumber())
                ->html($this->twig->render('emails/order_approved.html.twig', $emailData));

            $this->mailer->send($email);
            
            return true;
        } catch (\Exception $e) {
            error_log('Error sending order approval: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order rejection notification
     */
    public function sendOrderRejection(Order $order): bool
    {
        try {
            $setting = $this->getSettings();
            
            if (!$setting || !$order->getClientEmail()) {
                return false;
            }

            $emailData = [
                'order' => $order,
                'client_name' => $order->getClientName(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'rejection_reason' => $order->getRejectionReason(),
                'site_name' => $this->getSiteName(),
                'site_email' => $this->getSenderEmail(),
                'contact_email' => $this->getContactEmail()
            ];

            $email = (new Email())
                ->from(new Address($this->getSenderEmail(), $this->getSiteName()))
                ->to($order->getClientEmail())
                ->subject('Information importante concernant votre commande ' . $order->getOrderNumber())
                ->html($this->twig->render('emails/order_rejected.html.twig', $emailData));

            $this->mailer->send($email);
            
            return true;
        } catch (\Exception $e) {
            error_log('Error sending order rejection: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order confirmation (when order is created)
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        try {
            $setting = $this->getSettings();
            
            if (!$setting || !$order->getClientEmail()) {
                return false;
            }

            $emailData = [
                'order' => $order,
                'client_name' => $order->getClientName(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'site_name' => $this->getSiteName(),
                'site_email' => $this->getSenderEmail()
            ];

            $email = (new Email())
                ->from(new Address($this->getSenderEmail(), $this->getSiteName()))
                ->to($order->getClientEmail())
                ->subject('Confirmation de votre commande ' . $order->getOrderNumber())
                ->html($this->twig->render('emails/order_confirmation.html.twig', $emailData));

            $this->mailer->send($email);
            
            return true;
        } catch (\Exception $e) {
            error_log('Error sending order confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation(Order $order): bool
    {
        try {
            $setting = $this->getSettings();
            
            if (!$setting || !$order->getClientEmail()) {
                return false;
            }

            $emailData = [
                'order' => $order,
                'client_name' => $order->getClientName(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'paid_at' => $order->getPaidAt(),
                'site_name' => $this->getSiteName(),
                'site_email' => $this->getSenderEmail()
            ];

            $email = (new Email())
                ->from(new Address($this->getSenderEmail(), $this->getSiteName()))
                ->to($order->getClientEmail())
                ->subject('Paiement confirmé - ' . $order->getOrderNumber())
                ->html($this->twig->render('emails/payment_confirmed.html.twig', $emailData));

            $this->mailer->send($email);
            
            return true;
        } catch (\Exception $e) {
            error_log('Error sending payment confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send bulk payment reminders
     */
    public function sendBulkPaymentReminders(array $orders): int
    {
        $successCount = 0;
        
        foreach ($orders as $order) {
            if ($this->sendPaymentReminder($order)) {
                $successCount++;
            }
        }
        
        return $successCount;
    }

    /**
     * Send contact form notification to admin
     */
    public function sendContactNotification(ContactMessage $contact): bool
    {
        try {
            $setting = $this->getSettings();
            
            if (!$setting) {
                return false;
            }

            $emailData = [
                'contact' => $contact,
                'site_name' => $this->getSiteName(),
                'site_email' => $this->getSenderEmail()
            ];

            $email = (new Email())
                ->from(new Address($this->getSenderEmail(), $this->getSiteName()))
                ->to($this->getReceiverEmail())
                ->subject('Nouveau message de contact - ' . $contact->getSubject())
                ->html($this->twig->render('emails/contact_notification.html.twig', $emailData));

            $this->mailer->send($email);
            
            return true;
        } catch (\Exception $e) {
            error_log('Error sending contact notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send contact confirmation to sender
     */
    public function sendContactConfirmation(ContactMessage $contact): bool
    {
        try {
            $setting = $this->getSettings();
            
            if (!$setting || !$contact->getEmail()) {
                return false;
            }

            $emailData = [
                'contact' => $contact,
                'site_name' => $this->getSiteName(),
                'site_email' => $this->getSenderEmail(),
                'site_phone' => $setting->getPhone(),
                'site_address' => $setting->getAddress()
            ];

            $email = (new Email())
                ->from(new Address($this->getSenderEmail(), $this->getSiteName()))
                ->to($contact->getEmail())
                ->subject('Confirmation de votre message - ' . $this->getSiteName())
                ->html($this->twig->render('emails/contact_confirmation.html.twig', $emailData));

            $this->mailer->send($email);
            
            return true;
        } catch (\Exception $e) {
            error_log('Error sending contact confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send registration confirmation
     */
    public function sendRegistrationConfirmation(string $userEmail, string $userName): bool
    {
        try {
            $setting = $this->getSettings();
            
            if (!$setting) {
                return false;
            }

            $emailData = [
                'user_name' => $userName,
                'user_email' => $userEmail,
                'site_name' => $this->getSiteName(),
                'site_email' => $this->getSenderEmail()
            ];

            $email = (new Email())
                ->from(new Address($this->getSenderEmail(), $this->getSiteName()))
                ->to($userEmail)
                ->subject('Bienvenue sur ' . $this->getSiteName())
                ->html($this->twig->render('emails/registration_confirmation.html.twig', $emailData));

            $this->mailer->send($email);
            
            return true;
        } catch (\Exception $e) {
            error_log('Error sending registration confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment proof upload notification to admin
     */
    public function sendPaymentProofUploadNotification(Order $order): bool
    {
        try {
            $setting = $this->getSettings();
            
            if (!$setting) {
                return false;
            }

            $emailData = [
                'order' => $order,
                'client_name' => $order->getClientName(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'client_email' => $order->getClientEmail(),
                'client_phone' => $order->getClientPhone(),
                'site_name' => $this->getSiteName(),
                'site_email' => $this->getSenderEmail()
            ];

            $email = (new Email())
                ->from(new Address($this->getSenderEmail(), $this->getSiteName()))
                ->to($this->getReceiverEmail())
                ->subject('Nouveau justificatif de paiement - Commande ' . $order->getOrderNumber())
                ->html($this->twig->render('emails/payment_proof_uploaded.html.twig', $emailData));

            $this->mailer->send($email);
            
            return true;
        } catch (\Exception $e) {
            error_log('Error sending payment proof notification: ' . $e->getMessage());
            return false;
        }
    }
}