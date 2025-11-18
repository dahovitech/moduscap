<?php

namespace App\Controller\Admin;

use App\Entity\Setting;
use App\Form\SettingType;
use App\Repository\SettingRepository;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {}
    #[Route('/', name: 'dashboard')]
    public function dashboard(
        LanguageRepository $languageRepository
    ): Response {
        $languages = $languageRepository->getAllOrderedBySortOrder();

        return $this->render('admin/dashboard.html.twig', [
            'languages' => $languages,
            'admin_languages' => $languageRepository->findActiveLanguages(), // Ensure admin_languages is available
        ]);
    }

    #[Route('/setting', name: 'setting', methods: ["GET", "POST"])]
    public function setting(Request $request, SettingRepository $settingRepository, EntityManagerInterface $entityManager): Response
    {
        $setting = $settingRepository->findOneBy([], ['id' => 'desc']);

        if ($setting === null) {
            $setting = new Setting();
        }

        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Utilisation de Doctrine ORM pour la mise à jour de l'entité
            $entityManager->persist($setting);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('admin.errors.setting.success', [], 'admin'));
        }

        return $this->render('admin/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
