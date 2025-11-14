<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\Language;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LanguageRepository $languageRepository
    ) {
    }

    #[Route('', name: 'admin_product_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $category = $request->query->get('category', '');
        
        $qb = $this->entityManager->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.translations', 't')
            ->addSelect('c', 't');

        if ($search) {
            $qb->andWhere('p.code LIKE :search OR t.name LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($category) {
            $qb->andWhere('p.category = :category')
               ->setParameter('category', $category);
        }

        $products = $qb->orderBy('p.createdAt', 'DESC')->getQuery()->getResult();

        $categories = $this->entityManager->getRepository(\App\Entity\ProductCategory::class)
            ->findAll();

        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'current_search' => $search,
            'current_category' => $category,
        ]);
    }

    #[Route('/new', name: 'admin_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $product = new Product();
        $product->setCreatedAt(new \DateTime());
        $product->setUpdatedAt(new \DateTime());

        $form = $this->createForm(\App\Form\ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traiter les traductions manuellement depuis les données du formulaire
            $translationsData = $request->request->get('product', [])['translations'] ?? [];
            
            if ($translationsData) {
                foreach ($translationsData as $translationData) {
                    $language = $this->languageRepository->findOneBy(['code' => $translationData['language'] ?? '']);
                    
                    if ($language) {
                        $translation = new ProductTranslation();
                        $translation->setProduct($product);
                        $translation->setLanguage($language);
                        $translation->setName($translationData['name'] ?? '');
                        $translation->setDescription($translationData['description'] ?? '');
                        $translation->setConcept($translationData['concept'] ?? '');
                        $translation->setShortDescription($translationData['shortDescription'] ?? '');
                        $translation->setMaterialsDetail($translationData['materialsDetail'] ?? '');
                        $translation->setEquipmentDetail($translationData['equipmentDetail'] ?? '');
                        $translation->setPerformanceDetails($translationData['performanceDetails'] ?? '');
                        $translation->setSpecifications($translationData['specifications'] ?? '');
                        $translation->setAdvantages($translationData['advantages'] ?? '');
                        
                        $product->addTranslation($translation);
                    }
                }
            }
            
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Produit créé avec succès !');
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product): Response
    {
        // Mettre à jour le timestamp
        $product->setUpdatedAt(new \DateTime());
        
        $form = $this->createForm(\App\Form\ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Produit supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide !');
        }

        return $this->redirectToRoute('admin_product_index');
    }

    #[Route('/quick-update', name: 'admin_product_quick_update', methods: ['POST'])]
    public function quickUpdate(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $productId = $data['id'] ?? null;
        $field = $data['field'] ?? null;
        $value = $data['value'] ?? null;

        if (!$productId || !$field || $value === null) {
            return $this->json(['error' => 'Données invalides'], 400);
        }

        $product = $this->entityManager->getRepository(Product::class)->find($productId);
        
        if (!$product) {
            return $this->json(['error' => 'Produit non trouvé'], 404);
        }

        $method = 'set' . ucfirst($field);
        if (method_exists($product, $method)) {
            $product->$method($value);
            $product->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();
            
            return $this->json(['success' => true]);
        }

        return $this->json(['error' => 'Champ non valide'], 400);
    }

    #[Route('/duplicate/{id}', name: 'admin_product_duplicate', methods: ['POST'])]
    public function duplicate(Product $product): Response
    {
        $newProduct = clone $product;
        $newProduct->setCode($product->getCode() . '_copy_' . time());
        $newProduct->setCreatedAt(new \DateTime());
        $newProduct->setUpdatedAt(new \DateTime());
        
        // Dupliquer les traductions
        foreach ($product->getTranslations() as $translation) {
            $newTranslation = clone $translation;
            $newTranslation->setProduct($newProduct);
            $newProduct->addTranslation($newTranslation);
        }

        $this->entityManager->persist($newProduct);
        $this->entityManager->flush();

        $this->addFlash('success', 'Produit dupliqué avec succès !');
        return $this->redirectToRoute('admin_product_edit', ['id' => $newProduct->getId()]);
    }
}