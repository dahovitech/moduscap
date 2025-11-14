<?php

namespace App\Controller\Admin;

use App\Entity\ProductOption;
use App\Entity\ProductOptionGroup;
use App\Entity\ProductOptionGroupTranslation;
use App\Entity\ProductOptionTranslation;
use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Repository\ProductOptionGroupRepository;
use App\Repository\ProductOptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/options')]
class OptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LanguageRepository $languageRepository,
        private TranslatorInterface $translator
    ) {}

    #[Route('/', name: 'admin_option_index', methods: ['GET'])]
    public function index(ProductOptionGroupRepository $optionGroupRepository): Response
    {
        $optionGroups = $optionGroupRepository->findBy([], ['sortOrder' => 'ASC']);

        return $this->render('admin/option/index.html.twig', [
            'optionGroups' => $optionGroups,
        ]);
    }

    #[Route('/groups/new', name: 'admin_option_group_new', methods: ['GET', 'POST'])]
    public function newGroup(Request $request): Response
    {
        $optionGroup = new ProductOptionGroup();
        $optionGroup->setCreatedAt(new \DateTime());
        $optionGroup->setUpdatedAt(new \DateTime());
        
        // Initialiser les traductions pour toutes les langues actives
        $languages = $this->languageRepository->findBy(['active' => true]);
        
        foreach ($languages as $language) {
            $translation = new ProductOptionGroupTranslation();
            $translation->setOptionGroup($optionGroup);
            $translation->setLanguage($language);
            $translation->setName('');
            $translation->setDescription('');
            $optionGroup->addTranslation($translation);
        }

        $form = $this->createForm(\App\Form\ProductOptionGroupType::class, $optionGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($optionGroup);
            $this->entityManager->flush();

            $this->addFlash('success', 'Groupe d\'options créé avec succès !');
            return $this->redirectToRoute('admin_option_index');
        }

        return $this->render('admin/option/group_new.html.twig', [
            'optionGroup' => $optionGroup,
            'form' => $form->createView(),
            'languages' => $languages,
        ]);
    }

    #[Route('/groups/{id}/edit', name: 'admin_option_group_edit', methods: ['GET', 'POST'])]
    public function editGroup(Request $request, ProductOptionGroup $optionGroup): Response
    {
        $optionGroup->setUpdatedAt(new \DateTime());
        
        $form = $this->createForm(\App\Form\ProductOptionGroupType::class, $optionGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Groupe d\'options modifié avec succès !');
            return $this->redirectToRoute('admin_option_index');
        }

        return $this->render('admin/option/group_edit.html.twig', [
            'optionGroup' => $optionGroup,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/groups/{id}', name: 'admin_option_group_delete', methods: ['POST'])]
    public function deleteGroup(Request $request, ProductOptionGroup $optionGroup): Response
    {
        if ($this->isCsrfTokenValid('delete'.$optionGroup->getId(), $request->request->get('_token'))) {
            // Vérifier si des produits utilisent ce groupe
            if ($optionGroup->getOptions()->count() > 0) {
                $this->addFlash('error', 'Impossible de supprimer ce groupe d\'options car il contient encore des options.');
                return $this->redirectToRoute('admin_option_index');
            }

            $this->entityManager->remove($optionGroup);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Groupe d\'options supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide !');
        }

        return $this->redirectToRoute('admin_option_index');
    }

    #[Route('/groups/{groupId}/options/new', name: 'admin_option_new', methods: ['GET', 'POST'])]
    public function newOption(Request $request, int $groupId): Response
    {
        $optionGroup = $this->entityManager->getRepository(ProductOptionGroup::class)->find($groupId);
        
        if (!$optionGroup) {
            $this->addFlash('error', 'Groupe d\'options non trouvé.');
            return $this->redirectToRoute('admin_option_index');
        }

        $option = new ProductOption();
        $option->setOptionGroup($optionGroup);
        $option->setCreatedAt(new \DateTime());
        $option->setUpdatedAt(new \DateTime());
        
        // Initialiser les traductions pour toutes les langues actives
        $languages = $this->languageRepository->findBy(['active' => true]);
        
        foreach ($languages as $language) {
            $translation = new ProductOptionTranslation();
            $translation->setOption($option);
            $translation->setLanguage($language);
            $translation->setName('');
            $translation->setDescription('');
            $option->addTranslation($translation);
        }

        $form = $this->createForm(\App\Form\ProductOptionType::class, $option);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($option);
            $this->entityManager->flush();

            $this->addFlash('success', 'Option créée avec succès !');
            return $this->redirectToRoute('admin_option_index');
        }

        return $this->render('admin/option/option_new.html.twig', [
            'optionGroup' => $optionGroup,
            'option' => $option,
            'form' => $form->createView(),
            'languages' => $languages,
        ]);
    }

    #[Route('/options/{id}/edit', name: 'admin_option_edit', methods: ['GET', 'POST'])]
    public function editOption(Request $request, ProductOption $option): Response
    {
        $option->setUpdatedAt(new \DateTime());
        
        $form = $this->createForm(\App\Form\ProductOptionType::class, $option);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Option modifiée avec succès !');
            return $this->redirectToRoute('admin_option_index');
        }

        return $this->render('admin/option/option_edit.html.twig', [
            'option' => $option,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/options/{id}', name: 'admin_option_delete', methods: ['POST'])]
    public function deleteOption(Request $request, ProductOption $option): Response
    {
        if ($this->isCsrfTokenValid('delete'.$option->getId(), $request->request->get('_token'))) {
            // Vérifier si des produits utilisent cette option
            if ($option->getProducts()->count() > 0) {
                $this->addFlash('error', 'Impossible de supprimer cette option car elle est utilisée par des produits.');
                return $this->redirectToRoute('admin_option_index');
            }

            $this->entityManager->remove($option);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Option supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide !');
        }

        return $this->redirectToRoute('admin_option_index');
    }

    #[Route('/groups/{groupId}/options/reorder', name: 'admin_option_reorder', methods: ['POST'])]
    public function reorderOptions(Request $request, int $groupId): Response
    {
        $optionGroup = $this->entityManager->getRepository(ProductOptionGroup::class)->find($groupId);
        
        if (!$optionGroup) {
            return $this->json(['error' => 'Groupe d\'options non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $orderedIds = $data['ordered_ids'] ?? [];

        foreach ($orderedIds as $index => $optionId) {
            $option = $this->entityManager->getRepository(ProductOption::class)->find($optionId);
            if ($option && $option->getOptionGroup()->getId() === $groupId) {
                $option->setSortOrder($index);
                $option->setUpdatedAt(new \DateTime());
            }
        }

        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/groups/{groupId}/options/quick-update', name: 'admin_option_quick_update', methods: ['POST'])]
    public function quickUpdateOption(Request $request, int $groupId): Response
    {
        $optionGroup = $this->entityManager->getRepository(ProductOptionGroup::class)->find($groupId);
        
        if (!$optionGroup) {
            return $this->json(['error' => 'Groupe d\'options non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $optionId = $data['id'] ?? null;
        $field = $data['field'] ?? null;
        $value = $data['value'] ?? null;

        if (!$optionId || !$field || $value === null) {
            return $this->json(['error' => 'Données invalides'], 400);
        }

        $option = $this->entityManager->getRepository(ProductOption::class)->find($optionId);
        
        if (!$option || $option->getOptionGroup()->getId() !== $groupId) {
            return $this->json(['error' => 'Option non trouvée'], 404);
        }

        $method = 'set' . ucfirst($field);
        if (method_exists($option, $method)) {
            $option->$method($value);
            $option->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();
            
            return $this->json(['success' => true]);
        }

        return $this->json(['error' => 'Champ non valide'], 400);
    }
}