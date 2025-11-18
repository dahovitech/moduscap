<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Repository\LanguageRepository;
use App\Repository\ProductRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class FrontController
 * @package App\Controller
 */
#[Route('/{_locale}', requirements: ['_locale' => '[a-z]{2}'])]
class FrontController extends AbstractController
{
    public function __construct(
        private LanguageRepository $languageRepository,
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
        private EmailService $emailService,
        private ValidatorInterface $validator,
        private TranslatorInterface $translator
    ) {}

    /**
     * Homepage avec locale
     */
    #[Route('/', name: 'app_homepage')]
    public function homepage(Request $request): Response
    {
        $locale = $request->getLocale();
        $currentLanguage = $this->languageRepository->findByCode($locale);
        $featuredProducts = $this->productRepository->findFeaturedProducts($locale);

        return $this->render('@theme/homepage.html.twig', [
            'currentLanguage' => $currentLanguage,
            'locale' => $locale,
            'featured_products' => $featuredProducts
        ]);
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request): Response
    {
        $locale = $request->getLocale();
        $currentLanguage = $this->languageRepository->findByCode($locale);

        if ($request->isMethod('POST')) {
            $contactMessage = new ContactMessage();
            $contactMessage->setFirstName($request->request->get('first_name'));
            $contactMessage->setLastName($request->request->get('last_name'));
            $contactMessage->setEmail($request->request->get('email'));
            $contactMessage->setPhone($request->request->get('phone'));
            $contactMessage->setSubject($request->request->get('subject'));
            $contactMessage->setMessage($request->request->get('message'));
            $contactMessage->setIpAddress($request->getClientIp());

            $errors = $this->validator->validate($contactMessage);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            } else {
                $this->entityManager->persist($contactMessage);
                $this->entityManager->flush();

                // Send emails
                $this->emailService->sendContactNotification($contactMessage);
                $this->emailService->sendContactConfirmation($contactMessage);

                $this->addFlash('success', $this->translator->trans('contact.success_message', [], 'default'));
                return $this->redirectToRoute('app_contact', ['_locale' => $locale]);
            }
        }

        return $this->render('@theme/contact.html.twig', [
            'currentLanguage' => $currentLanguage,
            'locale' => $locale
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(Request $request): Response
    {
        $locale = $request->getLocale();
        $currentLanguage = $this->languageRepository->findByCode($locale);

        return $this->render('@theme/about.html.twig', [
            'currentLanguage' => $currentLanguage,
            'locale' => $locale
        ]);
    }

    #[Route('/services', name: 'app_services')]
    public function services(Request $request): Response
    {
        $locale = $request->getLocale();
        $currentLanguage = $this->languageRepository->findByCode($locale);

        return $this->render('@theme/services.html.twig', [
            'currentLanguage' => $currentLanguage,
            'locale' => $locale
        ]);
    }

    #[Route('/quote', name: 'app_quote')]
    public function quote(Request $request): Response
    {
        $locale = $request->getLocale();
        $currentLanguage = $this->languageRepository->findByCode($locale);

        return $this->render('@theme/quote.html.twig', [
            'currentLanguage' => $currentLanguage,
            'locale' => $locale
        ]);
    }

    #[Route('/legal', name: 'app_legal')]
    public function legal(Request $request): Response
    {
        $locale = $request->getLocale();
        $currentLanguage = $this->languageRepository->findByCode($locale);

        return $this->render('@theme/legal.html.twig', [
            'currentLanguage' => $currentLanguage,
            'locale' => $locale
        ]);
    }

    #[Route('/privacy', name: 'app_privacy')]
    public function privacy(Request $request): Response
    {
        $locale = $request->getLocale();
        $currentLanguage = $this->languageRepository->findByCode($locale);

        return $this->render('@theme/privacy.html.twig', [
            'currentLanguage' => $currentLanguage,
            'locale' => $locale
        ]);
    }

    #[Route('/terms', name: 'app_terms')]
    public function terms(Request $request): Response
    {
        $locale = $request->getLocale();
        $currentLanguage = $this->languageRepository->findByCode($locale);

        return $this->render('@theme/terms.html.twig', [
            'currentLanguage' => $currentLanguage,
            'locale' => $locale
        ]);
    }
}
