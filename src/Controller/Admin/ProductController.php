<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\ProductMedia;
use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;

#[Route('/admin/products')]
#[IsGranted('ROLE_ADMIN')]
class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LanguageRepository $languageRepository,
        private MediaRepository $mediaRepository,
        private TranslatorInterface $translator,
        private LoggerInterface $logger
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

        if ($form->isSubmitted()) {
            $productData = $request->request->all('product');
            $this->logger->info('Product form submitted', [
                'code' => $productData['code'] ?? 'N/A',
                'category' => $productData['category'] ?? 'N/A',
            ]);

            if ($form->isValid()) {
                try {
                    // Traiter les images
                    $this->handleProductMedia($request, $product);
                    
                    // Traiter les traductions manuellement depuis les données du formulaire
                    $translationsData = $productData['translations'] ?? [];
                    
                    if ($translationsData) {
                        $translationCount = 0;
                        foreach ($translationsData as $translationData) {
                            $languageCode = $translationData['language'] ?? '';
                            if (!$languageCode) {
                                continue;
                            }
                            
                            $language = $this->languageRepository->findOneBy(['code' => $languageCode]);
                            
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
                                $translationCount++;
                            }
                        }
                        $this->logger->info('Translations added', ['count' => $translationCount]);
                    } else {
                        $this->logger->warning('No translations submitted');
                    }
                    
                    // Persister le produit
                    $this->entityManager->persist($product);
                    $this->entityManager->flush();

                    $this->logger->info('Product created successfully', ['id' => $product->getId()]);
                    $this->addFlash('success', $this->translator->trans('admin.errors.product.created_successfully', [], 'admin'));
                    return $this->redirectToRoute('admin_product_index');
                    
                } catch (UniqueConstraintViolationException $e) {
                    $this->logger->error('Product code already exists', [
                        'code' => $product->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    
                    $this->addFlash('error', $this->translator->trans('admin.errors.product.code_already_exists', [], 'admin'));
                } catch (ORMException | \Exception $e) {
                    $this->logger->error('Error creating product', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    
                    $this->addFlash('error', $this->translator->trans('admin.errors.product.save_error', [], 'admin'));
                }
            } else {
                // Formulaire invalide - logger les erreurs
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                
                $this->logger->warning('Product form validation failed', ['errors' => $errors]);
                $this->addFlash('error', $this->translator->trans('admin.errors.product.validation_failed', [], 'admin'));
            }
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

        if ($form->isSubmitted()) {
            $productData = $request->request->all('product');
            $this->logger->info('Product edit form submitted', [
                'id' => $product->getId(),
                'code' => $productData['code'] ?? 'N/A',
            ]);

            if ($form->isValid()) {
                try {
                    // Traiter les images
                    $this->handleProductMedia($request, $product);
                    
                    // Traiter les traductions manuellement depuis les données du formulaire
                    $translationsData = $productData['translations'] ?? [];
                    
                    if ($translationsData) {
                        // Supprimer toutes les traductions existantes
                        foreach ($product->getTranslations() as $translation) {
                            $product->removeTranslation($translation);
                            $this->entityManager->remove($translation);
                        }
                        
                        // Ajouter les nouvelles traductions
                        $translationCount = 0;
                        foreach ($translationsData as $translationData) {
                            $languageCode = $translationData['language'] ?? '';
                            if (!$languageCode) {
                                continue;
                            }
                            
                            $language = $this->languageRepository->findOneBy(['code' => $languageCode]);
                            
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
                                $translationCount++;
                            }
                        }
                        $this->logger->info('Translations updated', ['count' => $translationCount]);
                    } else {
                        $this->logger->warning('No translations submitted during update');
                    }
                    
                    $this->entityManager->flush();

                    $this->logger->info('Product updated successfully', ['id' => $product->getId()]);
                    $this->addFlash('success', $this->translator->trans('admin.errors.product.updated_successfully', [], 'admin'));
                    return $this->redirectToRoute('admin_product_index');
                    
                } catch (UniqueConstraintViolationException $e) {
                    $this->logger->error('Product code already exists', [
                        'id' => $product->getId(),
                        'code' => $product->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    
                    $this->addFlash('error', $this->translator->trans('admin.errors.product.code_already_exists', [], 'admin'));
                } catch (ORMException | \Exception $e) {
                    $this->logger->error('Error updating product', [
                        'id' => $product->getId(),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    
                    $this->addFlash('error', $this->translator->trans('admin.errors.product.save_error', [], 'admin'));
                }
            } else {
                // Formulaire invalide - logger les erreurs
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                
                $this->logger->warning('Product edit form validation failed', [
                    'id' => $product->getId(),
                    'errors' => $errors
                ]);
                $this->addFlash('error', $this->translator->trans('admin.errors.product.validation_failed', [], 'admin'));
            }
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Gérer les médias du produit
     */
    private function handleProductMedia(Request $request, Product $product): void
    {
        $data = $request->request->all();
        
        // Supprimer toutes les associations media existantes
        foreach ($product->getProductMedia() as $productMedia) {
            $this->entityManager->remove($productMedia);
        }
        $product->getProductMedia()->clear();
        
        // Image principale
        if (isset($data['main_image_id']) && !empty($data['main_image_id'])) {
            $media = $this->mediaRepository->find($data['main_image_id']);
            if ($media) {
                $productMedia = new ProductMedia();
                $productMedia->setProduct($product);
                $productMedia->setMedia($media);
                $productMedia->setMediaType('main_image');
                $productMedia->setIsMainImage(true);
                $productMedia->setSortOrder(0);
                $product->addProductMedia($productMedia);
                $this->entityManager->persist($productMedia);
            }
        }
        
        // Images de galerie
        if (isset($data['gallery_images']) && is_array($data['gallery_images'])) {
            $sortOrder = 1;
            foreach ($data['gallery_images'] as $mediaId) {
                if (!empty($mediaId)) {
                    $media = $this->mediaRepository->find($mediaId);
                    if ($media) {
                        $productMedia = new ProductMedia();
                        $productMedia->setProduct($product);
                        $productMedia->setMedia($media);
                        $productMedia->setMediaType('gallery');
                        $productMedia->setIsMainImage(false);
                        $productMedia->setSortOrder($sortOrder++);
                        $product->addProductMedia($productMedia);
                        $this->entityManager->persist($productMedia);
                    }
                }
            }
        }
        
        // Images techniques
        if (isset($data['technical_images']) && is_array($data['technical_images'])) {
            $sortOrder = 100; // Commencer à 100 pour les images techniques
            foreach ($data['technical_images'] as $mediaId) {
                if (!empty($mediaId)) {
                    $media = $this->mediaRepository->find($mediaId);
                    if ($media) {
                        $productMedia = new ProductMedia();
                        $productMedia->setProduct($product);
                        $productMedia->setMedia($media);
                        $productMedia->setMediaType('technical');
                        $productMedia->setIsMainImage(false);
                        $productMedia->setSortOrder($sortOrder++);
                        $product->addProductMedia($productMedia);
                        $this->entityManager->persist($productMedia);
                    }
                }
            }
        }
    }

    #[Route('/{id}', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
            
            $this->addFlash('success', $this->translator->trans('admin.errors.product.deleted_successfully', [], 'admin'));
        } else {
            $this->addFlash('error', $this->translator->trans('admin.errors.product.csrf_token_invalid', [], 'admin'));
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
            return $this->json(['error' => $this->translator->trans('admin.errors.product.data_invalid', [], 'admin')], 400);
        }

        $product = $this->entityManager->getRepository(Product::class)->find($productId);
        
        if (!$product) {
            return $this->json(['error' => $this->translator->trans('admin.errors.product.not_found', [], 'admin')], 404);
        }

        $method = 'set' . ucfirst($field);
        if (method_exists($product, $method)) {
            $product->$method($value);
            $product->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();
            
            return $this->json(['success' => true]);
        }

        return $this->json(['error' => $this->translator->trans('admin.errors.product.field_invalid', [], 'admin')], 400);
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

        $this->addFlash('success', $this->translator->trans('admin.errors.product.duplicated_successfully', [], 'admin'));
        return $this->redirectToRoute('admin_product_edit', ['id' => $newProduct->getId()]);
    }

    #[Route('/{id}/images', name: 'admin_product_images', methods: ['GET', 'POST'])]
    public function manageImages(Request $request, Product $product): Response
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $action = $data['action'] ?? '';
            $mediaId = $data['media_id'] ?? null;
            $mediaType = $data['media_type'] ?? 'gallery';
            $isMainImage = $data['is_main_image'] ?? false;
            $sortOrder = $data['sort_order'] ?? 0;

            $mediaRepository = $this->entityManager->getRepository(\App\Entity\Media::class);
            $productMediaRepository = $this->entityManager->getRepository(\App\Entity\ProductMedia::class);

            if ($action === 'add' && $mediaId) {
                $media = $mediaRepository->find($mediaId);
                if ($media) {
                    // Vérifier si l'image existe déjà pour ce produit
                    $existingProductMedia = $productMediaRepository->findOneBy([
                        'product' => $product,
                        'media' => $media
                    ]);

                    if (!$existingProductMedia) {
                        $productMedia = new \App\Entity\ProductMedia();
                        $productMedia->setProduct($product);
                        $productMedia->setMedia($media);
                        $productMedia->setMediaType($mediaType);
                        $productMedia->setIsMainImage($isMainImage);
                        $productMedia->setSortOrder($sortOrder);
                        
                        $this->entityManager->persist($productMedia);
                        $this->entityManager->flush();

                        return $this->json([
                            'success' => true,
                            'message' => $this->translator->trans('admin.errors.product.image_added_successfully', [], 'admin'),
                            'product_media' => [
                                'id' => $productMedia->getId(),
                                'media_type' => $productMedia->getMediaType(),
                                'is_main_image' => $productMedia->isMainImage(),
                                'media' => [
                                    'id' => $media->getId(),
                                    'file_name' => $media->getFileName(),
                                    'alt' => $media->getAlt(),
                                    'web_path' => $media->getWebPath(),
                                    'url' => '/' . $media->getWebPath()
                                ]
                            ]
                        ]);
                    }
                }
            } elseif ($action === 'remove' && $mediaId) {
                $productMedia = $productMediaRepository->findOneBy([
                    'product' => $product,
                    'media' => $mediaId
                ]);

                if ($productMedia) {
                    $this->entityManager->remove($productMedia);
                    $this->entityManager->flush();

                    return $this->json([
                        'success' => true,
                        'message' => $this->translator->trans('admin.errors.product.image_deleted_successfully', [], 'admin')
                    ]);
                }
            } elseif ($action === 'update') {
                $productMediaId = $data['product_media_id'] ?? null;
                if ($productMediaId) {
                    $productMedia = $productMediaRepository->find($productMediaId);
                    if ($productMedia && $productMedia->getProduct() === $product) {
                        if (isset($data['media_type'])) {
                            $productMedia->setMediaType($data['media_type']);
                        }
                        if (isset($data['is_main_image'])) {
                            $productMedia->setIsMainImage($data['is_main_image']);
                        }
                        if (isset($data['sort_order'])) {
                            $productMedia->setSortOrder($data['sort_order']);
                        }
                        
                        $this->entityManager->flush();

                        return $this->json([
                            'success' => true,
                            'message' => $this->translator->trans('admin.errors.product.image_updated_successfully', [], 'admin')
                        ]);
                    }
                }
            }

            return $this->json([
                'success' => false,
                'message' => $this->translator->trans('admin.errors.product.action_invalid', [], 'admin')
            ], 400);
        }

        // GET: Afficher la page de gestion des images
        return $this->render('admin/product/images.html.twig', [
            'product' => $product,
            'media_types' => [
                'main_image' => $this->translator->trans('admin.errors.product.media_types.main_image', [], 'admin'),
                'gallery' => $this->translator->trans('admin.errors.product.media_types.gallery', [], 'admin'),
                'technical' => $this->translator->trans('admin.errors.product.media_types.technical', [], 'admin'),
                'lifestyle' => $this->translator->trans('admin.errors.product.media_types.lifestyle', [], 'admin')
            ]
        ]);
    }
}