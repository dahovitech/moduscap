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
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Send payment reminder email
     */
    public function sendPaymentReminder(Order $order): bool
    {
        try {
            $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
            
            if (!$setting || !$order->getClientEmail()) {
                return false;
            }

            $emailData = [
                'order' => $order,
                'client_name' => $order->getClientName(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'payment_info' => $setting->getPaymentInfo(),
                'site_name' => $setting->getSiteName() ?: 'MODUSCAP',
                'site_email' => $setting->getEmailSender() ?: 'noreply@moduscap.com'
            ];

            $email = (new Email())
                ->from(new Address($setting->getEmailSender() ?: 'noreply@moduscap.com', $setting->getSiteName() ?: 'MODUSCAP'))
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
            $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
            
            if (!$setting || !$order->getClientEmail()) {
                return false;
            }

            $emailData = [
                'order' => $order,
                'client_name' => $order->getClientName(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'payment_info' => $setting->getPaymentInfo(),
                'site_name' => $setting->getSiteName() ?: 'MODUSCAP',
                'site_email' => $setting->getEmailSender() ?: 'noreply@moduscap.com'
            ];

            $email = (new Email())
                ->from(new Address($setting->getEmailSender() ?: 'noreply@moduscap.com', $setting->getSiteName() ?: 'MODUSCAP'))
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
            $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
            
            if (!$setting || !$order->getClientEmail()) {
                return false;
            }

            $emailData = [
                'order' => $order,
                'client_name' => $order->getClientName(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'rejection_reason' => $order->getRejectionReason(),
                'site_name' => $setting->getSiteName() ?: 'MODUSCAP',
                'site_email' => $setting->getEmailSender() ?: 'noreply@moduscap.com',
                'contact_email' => $setting->getEmail() ?: 'contact@moduscap.com'
            ];

            $email = (new Email())
                ->from(new Address($setting->getEmailSender() ?: 'noreply@moduscap.com', $setting->getSiteName() ?: 'MODUSCAP'))
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
            $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
            
            if (!$setting || !$order->getClientEmail()) {
                return false;
            }

            $emailData = [
                'order' => $order,
                'client_name' => $order->getClientName(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'site_name' => $setting->getSiteName() ?: 'MODUSCAP',
                'site_email' => $setting->getEmailSender() ?: 'noreply@moduscap.com'
            ];

            $email = (new Email())
                ->from(new Address($setting->getEmailSender() ?: 'noreply@moduscap.com', $setting->getSiteName() ?: 'MODUSCAP'))
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
            $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
            
            if (!$setting || !$order->getClientEmail()) {
                return false;
            }

            $emailData = [
                'order' => $order,
                'client_name' => $order->getClientName(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'paid_at' => $order->getPaidAt(),
                'site_name' => $setting->getSiteName() ?: 'MODUSCAP',
                'site_email' => $setting->getEmailSender() ?: 'noreply@moduscap.com'
            ];

            $email = (new Email())
                ->from(new Address($setting->getEmailSender() ?: 'noreply@moduscap.com', $setting->getSiteName() ?: 'MODUSCAP'))
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
            $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
            
            if (!$setting) {
                return false;
            }

            $receiverEmail = $setting->getEmailReceived() ?: $setting->getEmail() ?: 'contact@moduscap.com';

            $emailData = [
                'contact' => $contact,
                'site_name' => $setting->getSiteName() ?: 'MODUSCAP',
                'site_email' => $setting->getEmailSender() ?: 'noreply@moduscap.com'
            ];

            $email = (new Email())
                ->from(new Address($setting->getEmailSender() ?: 'noreply@moduscap.com', $setting->getSiteName() ?: 'MODUSCAP'))
                ->to($receiverEmail)
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
            $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
            
            if (!$setting || !$contact->getEmail()) {
                return false;
            }

            $emailData = [
                'contact' => $contact,
                'site_name' => $setting->getSiteName() ?: 'MODUSCAP',
                'site_email' => $setting->getEmailSender() ?: 'noreply@moduscap.com',
                'site_phone' => $setting->getPhone(),
                'site_address' => $setting->getAddress()
            ];

            $email = (new Email())
                ->from(new Address($setting->getEmailSender() ?: 'noreply@moduscap.com', $setting->getSiteName() ?: 'MODUSCAP'))
                ->to($contact->getEmail())
                ->subject('Confirmation de votre message - ' . ($setting->getSiteName() ?: 'MODUSCAP'))
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
            $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
            
            if (!$setting) {
                return false;
            }

            $emailData = [
                'user_name' => $userName,
                'user_email' => $userEmail,
                'site_name' => $setting->getSiteName() ?: 'MODUSCAP',
                'site_email' => $setting->getEmailSender() ?: 'noreply@moduscap.com'
            ];

            $email = (new Email())
                ->from(new Address($setting->getEmailSender() ?: 'noreply@moduscap.com', $setting->getSiteName() ?: 'MODUSCAP'))
                ->to($userEmail)
                ->subject('Bienvenue sur ' . ($setting->getSiteName() ?: 'MODUSCAP'))
                ->html($this->twig->render('emails/registration_confirmation.html.twig', $emailData));

            $this->mailer->send($email);
            
            return true;
        } catch (\Exception $e) {
            error_log('Error sending registration confirmation: ' . $e->getMessage());
            return false;
        }
    }
}