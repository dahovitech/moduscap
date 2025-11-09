<?php

namespace App\Controller\Admin;

use App\Entity\Language;
use App\Form\LanguageType;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/languages', name: 'admin_language_')]
class LanguageController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(LanguageRepository $languageRepository): Response
    {
        $languages = $languageRepository->getAllOrderedBySortOrder();

        return $this->render('admin/language/index.html.twig', [
            'languages' => $languages,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $language = new Language();
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($language->isDefault()) {
                $this->unsetAllDefaultLanguages($entityManager);
            }

            $entityManager->persist($language);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('language.created_success'));

            return $this->redirectToRoute('admin_language_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/language/new.html.twig', [
            'language' => $language,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Language $language): Response
    {
        return $this->render('admin/language/show.html.twig', [
            'language' => $language,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Language $language, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($language->isDefault()) {
                $this->unsetAllDefaultLanguages($entityManager);
            }

            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('language.updated_success'));

            return $this->redirectToRoute('admin_language_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/language/edit.html.twig', [
            'language' => $language,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Language $language, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$language->getId(), $request->getPayload()->get('_token'))) {
            if ($language->isDefault()) {
                $this->addFlash('error', $this->translator->trans('language.cannot_delete_default'));
            } else {
                $entityManager->remove($language);
                $entityManager->flush();
                $this->addFlash('success', $this->translator->trans('language.deleted_success'));
            }
        }

        return $this->redirectToRoute('admin_language_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/toggle-status', name: 'toggle_status', methods: ['POST'])]
    public function toggleStatus(Language $language, EntityManagerInterface $entityManager): Response
    {
        if ($language->isDefault() && $language->isActive()) {
            $this->addFlash('error', $this->translator->trans('language.cannot_deactivate_default'));
        } else {
            $language->setIsActive(!$language->isActive());
            $entityManager->flush();

            $statusKey = $language->isActive() ? 'language.activated_success' : 'language.deactivated_success';
            $this->addFlash('success', $this->translator->trans($statusKey));
        }

        return $this->redirectToRoute('admin_language_index');
    }

    #[Route('/{id}/set-default', name: 'set_default', methods: ['POST'])]
    public function setDefault(Language $language, EntityManagerInterface $entityManager, LanguageRepository $languageRepository): Response
    {
        if (!$language->isActive()) {
            $this->addFlash('error', $this->translator->trans('language.cannot_set_inactive_as_default'));
        } else {
            $languageRepository->setAsDefault($language);
            $this->addFlash('success', $this->translator->trans('language.set_as_default_success'));
        }

        return $this->redirectToRoute('admin_language_index');
    }

    private function unsetAllDefaultLanguages(EntityManagerInterface $entityManager): void
    {
        $entityManager->createQuery('UPDATE App\Entity\Language l SET l.isDefault = false WHERE l.isDefault = true')
            ->execute();
    }
}