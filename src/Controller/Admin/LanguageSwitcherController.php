<?php

namespace App\Controller\Admin;

use App\Repository\LanguageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/language', name: 'language_')]
class LanguageSwitcherController extends AbstractController
{
    public function __construct(
        private LanguageRepository $languageRepository,
        private TranslatorInterface $translator
    ) {}

    #[Route('/switch/{locale}', name: 'switch', methods: ['GET', 'POST'])]
    public function switch(string $locale, Request $request): JsonResponse|Response
    {
        try {
            $language = $this->languageRepository->findActiveByCode($locale);
           
            if (!$language) {
                $message = $this->translator->trans('admin.errors.language_switch.invalid_or_inactive', ['%locale%' => $locale], 'admin');

                if ($request->isXmlHttpRequest() || $request->getContentTypeFormat() === 'json') {
                    return new JsonResponse([
                        'success' => false,
                        'error' => $message
                    ], 400);
                }

                $this->addFlash('error', $message);
                return $this->redirectToRoute('admin_dashboard');
            }

            $session = $request->getSession();
            $session->set('_locale', $locale);
            $request->setLocale($locale);

            if ($request->isXmlHttpRequest() || $request->getContentTypeFormat() === 'json') {
                return new JsonResponse([
                    'success' => true,
                    'locale' => $locale,
                    'language' => $language->getNativeName()
                ]);
            }
           
            $referer = $request->headers->get('referer');
             
            if ($referer && str_starts_with($referer, $request->getSchemeAndHttpHost())) {
                return $this->redirect($referer);
            }

            return $this->redirectToRoute('admin_dashboard');

        } catch (\Exception $e) {
            $message = $this->translator->trans('admin.errors.language_switch.error_switching', ['%error%' => $e->getMessage()], 'admin');

            if ($request->isXmlHttpRequest() || $request->getContentTypeFormat() === 'json') {
                return new JsonResponse([
                    'success' => false,
                    'error' => $message
                ], 500);
            }

            $this->addFlash('error', $message);
            return $this->redirectToRoute('admin_dashboard');
        }
    }

    #[Route('/current', name: 'current')]
    public function current(Request $request): JsonResponse
    {
        $locale = $request->getLocale();
        $language = $this->languageRepository->findByCode($locale);

        return new JsonResponse([
            'locale' => $locale,
            'language' => $language ? $language->getNativeName() : $locale
        ]);
    }

    #[Route('/available', name: 'available')]
    public function available(): JsonResponse
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $defaultLanguage = $this->languageRepository->findDefaultLanguage();

        $result = [];
        foreach ($languages as $language) {
            $result[] = [
                'code' => $language->getCode(),
                'name' => $language->getNativeName(),
                'default' => $defaultLanguage && $defaultLanguage->getId() === $language->getId()
            ];
        }

        return new JsonResponse($result);
    }
}