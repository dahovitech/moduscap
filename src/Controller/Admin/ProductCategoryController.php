<?php

namespace App\Controller\Admin;

use App\Entity\ProductCategory;
use App\Entity\ProductCategoryTranslation;
use App\Entity\Language;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categories')]
class ProductCategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LanguageRepository $languageRepository
    ) {
    }

    #[Route('', name: 'admin_category_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        
        $qb = $this->entityManager->getRepository(ProductCategory::class)
            ->createQueryBuilder('c')
            ->leftJoin('c.translations', 't')
            ->addSelect('t');

        if ($search) {
            $qb->andWhere('c.code LIKE :search OR t.name LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $categories = $qb->orderBy('c.sortOrder', 'ASC')->getQuery()->getResult();

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
            'current_search' => $search,
        ]);
    }

    #[Route('/new', name: 'admin_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new ProductCategory();
        $category->setCreatedAt(new \DateTime());
        $category->setUpdatedAt(new \DateTime());
        
        // Initialiser les traductions pour toutes les langues actives
        $languages = $this->languageRepository->findBy(['active' => true]);
        
        foreach ($languages as $language) {
            $translation = new ProductCategoryTranslation();
            $translation->setCategory($category);
            $translation->setLanguage($language);
            $translation->setName('');
            $translation->setDescription('');
            $category->addTranslation($translation);
        }

        $form = $this->createForm(\App\Form\ProductCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Catégorie créée avec succès !');
            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
            'languages' => $languages,
        ]);
    }

    #[Route('/{id}', name: 'admin_category_show', methods: ['GET'])]
    public function show(ProductCategory $category): Response
    {
        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProductCategory $category): Response
    {
        // Mettre à jour le timestamp
        $category->setUpdatedAt(new \DateTime());
        
        $form = $this->createForm(\App\Form\ProductCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès !');
            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(Request $request, ProductCategory $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Catégorie supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide !');
        }

        return $this->redirectToRoute('admin_category_index');
    }

    #[Route('/reorder', name: 'admin_category_reorder', methods: ['POST'])]
    public function reorder(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $orderedIds = $data['ordered_ids'] ?? [];

        foreach ($orderedIds as $index => $categoryId) {
            $category = $this->entityManager->getRepository(ProductCategory::class)->find($categoryId);
            if ($category) {
                $category->setSortOrder($index);
                $category->setUpdatedAt(new \DateTime());
            }
        }

        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/quick-update', name: 'admin_category_quick_update', methods: ['POST'])]
    public function quickUpdate(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $categoryId = $data['id'] ?? null;
        $field = $data['field'] ?? null;
        $value = $data['value'] ?? null;

        if (!$categoryId || !$field || $value === null) {
            return $this->json(['error' => 'Données invalides'], 400);
        }

        $category = $this->entityManager->getRepository(ProductCategory::class)->find($categoryId);
        
        if (!$category) {
            return $this->json(['error' => 'Catégorie non trouvée'], 404);
        }

        $method = 'set' . ucfirst($field);
        if (method_exists($category, $method)) {
            $category->$method($value);
            $category->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();
            
            return $this->json(['success' => true]);
        }

        return $this->json(['error' => 'Champ non valide'], 400);
    }

    #[Route('/duplicate/{id}', name: 'admin_category_duplicate', methods: ['POST'])]
    public function duplicate(ProductCategory $category): Response
    {
        $newCategory = clone $category;
        $newCategory->setCode($category->getCode() . '_copy_' . time());
        $newCategory->setCreatedAt(new \DateTime());
        $newCategory->setUpdatedAt(new \DateTime());
        
        // Dupliquer les traductions
        foreach ($category->getTranslations() as $translation) {
            $newTranslation = clone $translation;
            $newTranslation->setCategory($newCategory);
            $newCategory->addTranslation($newTranslation);
        }

        $this->entityManager->persist($newCategory);
        $this->entityManager->flush();

        $this->addFlash('success', 'Catégorie dupliquée avec succès !');
        return $this->redirectToRoute('admin_category_edit', ['id' => $newCategory->getId()]);
    }
}