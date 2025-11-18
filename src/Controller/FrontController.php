<?php

namespace App\Controller;

use App\Repository\LanguageRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class FrontController
 * @package App\Controller
 */
#[Route('/{_locale}', requirements: ['_locale' => '[a-z]{2}'])]
class FrontController extends AbstractController
{
    public function __construct(
        private LanguageRepository $languageRepository,
        private ProductRepository $productRepository
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

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request): Response
    {
        $locale = $request->getLocale();
        $currentLanguage = $this->languageRepository->findByCode($locale);

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
}
