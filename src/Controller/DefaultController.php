<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 *
 */
class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    public function homepage(Request $request): Response
    {
        $locale = $request->getLocale();

        return $this->redirectToRoute('app_homepage', [
            '_locale' => $locale
        ]);
    }

    #[Route('/localeswitch/{_locale}', name: 'locale_switch', methods: ['GET'])]
    public function locale($_locale, Request $request, EntityManagerInterface $manager): RedirectResponse
    {
        $user = $this->getUser();
        if ($user) {
            $manager->flush();
        }
        
        // Set the new locale in session
        $request->getSession()->set('_locale', $_locale);
        
        // Get the referer URL
        $referer = $request->headers->get('referer');
        
        // If there's a referer, try to redirect to the same page with new locale
        if ($referer) {
            // Parse the referer URL to extract the path
            $refererPath = parse_url($referer, PHP_URL_PATH);
            
            // Replace the old locale with the new one in the path
            $currentLocale = $request->getLocale();
            if ($refererPath && $currentLocale) {
                // Pattern to match locale in URL like /fr/ or /en/
                $pattern = '#/' . preg_quote($currentLocale, '#') . '/#';
                $newPath = preg_replace($pattern, '/' . $_locale . '/', $refererPath, 1);
                
                // If locale was replaced, redirect to the new path
                if ($newPath !== $refererPath) {
                    return $this->redirect($newPath);
                }
            }
            
            // If we couldn't replace the locale, just redirect to referer
            return $this->redirect($referer);
        }
        
        // Fallback to homepage with new locale
        return $this->redirectToRoute('app_homepage', [
            '_locale' => $_locale
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
