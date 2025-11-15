<?php

namespace App\DataFixtures;

use App\Entity\Media;
use App\Entity\Product;
use App\Entity\ProductMedia;
use App\Entity\Language;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * MediaFixtures amélioré avec support multilingue et configuration externalisée
 * 
 * Améliorations apportées :
 * - Support multilingue pour les alt texts
 * - Configuration externalisée
 * - Meilleure gestion d'erreurs avec logging
 * - Optimisation des performances
 * - Validation des données
 * - Structure plus maintenable
 */
class MediaFixtures extends Fixture
{
    // Configuration externalisée des médias par produit
    private const MEDIA_CONFIG = [
        'capsule-house' => [
            'main' => 'o_1j3q9vbbdsgf22hil31kue7sda_15074.png',
            'gallery' => [
                'o_1j3qafmjg81m589fpk23b1sutk_23664.jpg',
                'o_1j47ae3vvv1eck1q5kbvk1p478_64516_12425.jpg',
                'o_1j47ag4v1a1i1jupin6m27g6c_71300_52290_39204_13379.jpg'
            ],
            'alt_texts' => [
                'fr' => [
                    'main' => 'Maison capsule moderne - Vue principale',
                    'gallery' => [
                        'Intérieur de la maison capsule',
                        'Vue extérieure détaillée',
                        'Détails architecturaux de la capsule'
                    ]
                ],
                'en' => [
                    'main' => 'Modern capsule house - Main view',
                    'gallery' => [
                        'Capsule house interior',
                        'Detailed exterior view',
                        'Architectural details of the capsule'
                    ]
                ]
            ]
        ],
        'apple-cabin' => [
            'main' => 'o_1j3qbs92qof98h11oj2s551edf9.jpg',
            'gallery' => [
                'o_1j3qc5q8h1hgh1dv56fs1hdk10m9k.jpg',
                'o_1j3qf369j1fvtg4h1jv114v51aqc8.jpg'
            ],
            'alt_texts' => [
                'fr' => [
                    'main' => 'Cabane Apple en bois - Vue principale',
                    'gallery' => [
                        'Intérieur cozy de la cabane',
                        'Ambiance chaleureux de la cabane Apple'
                    ]
                ],
                'en' => [
                    'main' => 'Apple wooden cabin - Main view',
                    'gallery' => [
                        'Cozy interior of the cabin',
                        'Warm atmosphere of the Apple cabin'
                    ]
                ]
            ]
        ],
        'natural-house' => [
            'main' => 'o_1j3qbcbqgpmc14r51ac41sghotsg.jpg',
            'gallery' => [
                'o_1j3qfjorkedkj92pjuao8vr8.jpg',
                'o_1j3qg0nr8hj010lb1p36lv16ia8.jpg',
                'o_1j3qg2kpupkt4tprdk198d1g269_28910.jpg'
            ],
            'alt_texts' => [
                'fr' => [
                    'main' => 'Maison naturelle écologique - Vue principale',
                    'gallery' => [
                        'Matériaux naturels de la maison',
                        'Vue d\'ensemble de l\'architecture',
                        'Détails de construction écologique'
                    ]
                ],
                'en' => [
                    'main' => 'Ecological natural house - Main view',
                    'gallery' => [
                        'Natural materials of the house',
                        'Overview of the architecture',
                        'Ecological construction details'
                    ]
                ]
            ]
        ],
        'dome-house' => [
            'main' => 'o_1igb671gubt3b68135i17appagb_33825.jpg',
            'gallery' => [
                'o_1igb6fo0j76m1cmmko11ke41r0c8_71287.jpg',
                'o_1j3qavh2815l09jcntfolqirhb.jpg',
                'o_1j3qb0q9718d01sv61op81339r6cb.jpg'
            ],
            'alt_texts' => [
                'fr' => [
                    'main' => 'Maison dôme futuriste - Vue principale',
                    'gallery' => [
                        'Architecture unique en forme de dôme',
                        'Design moderne et innovant',
                        'Structure géodésique détaillée'
                    ]
                ],
                'en' => [
                    'main' => 'Futuristic dome house - Main view',
                    'gallery' => [
                        'Unique dome-shaped architecture',
                        'Modern and innovative design',
                        'Detailed geodesic structure'
                    ]
                ]
            ]
        ],
        'model-double' => [
            'main' => 'o_1iou50gg5k8l1lsfhrtqdd1b61t_12826_67060_47166_74242_54459.jpg',
            'gallery' => [
                'o_1j3qev1bh6si13va1nac1b6v1nd98.jpg',
                'o_1j3qfb97l1scngf01h7g151s1h6a8.jpg'
            ],
            'alt_texts' => [
                'fr' => [
                    'main' => 'Modèle double spacieux - Vue principale',
                    'gallery' => [
                        'Espace double optimal',
                        'Aménagement intérieur fonctionnel'
                    ]
                ],
                'en' => [
                    'main' => 'Spacious double model - Main view',
                    'gallery' => [
                        'Optimal double space',
                        'Functional interior layout'
                    ]
                ]
            ]
        ]
    ];

    // Mapping des noms de dossiers par catégorie
    private const FOLDER_MAPPINGS = [
        'capsule-house' => 'Capsule House',
        'apple-cabin' => 'Apple Cabin',
        'natural-house' => 'Natural House',
        'dome-house' => 'Dome House',
        'model-double' => 'Model Double',
    ];

    // Mapping des types MIME par extension
    private const MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp'
    ];

    /**
     * Constructor simplifié pour compatibilité maximale
     * Les améliorations de logging sont conservées avec des méthodes simples
     */

    public function load(ObjectManager $manager): void
    {
        echo "Début du chargement des fixtures média\n";

        try {
            // Récupération optimisée des produits avec leurs catégories
            $products = $manager->getRepository(Product::class)->findAll();
            
            if (empty($products)) {
                echo "Aucun produit trouvé pour créer les médias\n";
                return;
            }

            echo count($products) . ' produits trouvés pour la création de médias\n";

            // Traitement des produits par batch pour optimiser les performances
            $this->processProductsInBatches($manager, $products);

            $manager->flush();
            echo "Fixtures média chargées avec succès\n";

        } catch (\Exception $e) {
            echo "Erreur lors du chargement des fixtures média: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Traite les produits par batch pour optimiser les performances
     */
    private function processProductsInBatches(ObjectManager $manager, array $products, int $batchSize = 10): void
    {
        $processed = 0;
        
        foreach ($products as $product) {
            try {
                $this->createProductImages($manager, $product);
                $processed++;
                
                // Flush périodique pour éviter la saturation mémoire
                if ($processed % $batchSize === 0) {
                    $manager->flush();
                    echo "Batch de {$batchSize} produits traité (processed: {$processed})\n";
                }
                
            } catch (\Exception $e) {
                echo "Erreur lors du traitement du produit: " . $product->getCode() . " - " . $e->getMessage() . "\n";
                // Continue avec les autres produits même en cas d'erreur
            }
        }
    }

    private function createProductImages(ObjectManager $manager, Product $product): void
    {
        $productCode = $product->getCode();
        
        // Vérification de la configuration pour ce produit
        if (!isset(self::MEDIA_CONFIG[$productCode])) {
            echo "Aucun média configuré pour le produit: {$productCode}\n";
            return;
        }

        $mediaConfig = self::MEDIA_CONFIG[$productCode];
        echo "Traitement des médias pour le produit: {$productCode}\n";

        // Création de l'image principale
        if (isset($mediaConfig['main'])) {
            $mainImagePath = $this->validateImagePath($product, $mediaConfig['main']);
            if ($mainImagePath) {
                $altText = $this->getAltTextForImage($product, 'main', $mediaConfig, $manager);
                $this->createMedia($manager, $product, $mediaConfig['main'], 'main_image', true, 0, $altText, $mainImagePath);
            }
        }

        // Création des images de galerie
        if (isset($mediaConfig['gallery']) && is_array($mediaConfig['gallery'])) {
            $sortOrder = 1;
            foreach ($mediaConfig['gallery'] as $index => $galleryImage) {
                $galleryImagePath = $this->validateImagePath($product, $galleryImage);
                if ($galleryImagePath) {
                    $altText = $this->getAltTextForImage($product, 'gallery', $mediaConfig, $manager, $index);
                    $this->createMedia($manager, $product, $galleryImage, 'gallery', false, $sortOrder, $altText, $galleryImagePath);
                    $sortOrder++;
                }
            }
        }
    }

    /**
     * Valide l'existence du fichier image et retourne le chemin complet
     */
    private function validateImagePath(Product $product, string $fileName): ?string
    {
        // Essai avec le code du produit
        $imagePath = $this->buildImagePath($product->getCode(), $fileName);
        if (file_exists($imagePath)) {
            return $imagePath;
        }

        // Essai avec le nom de dossier de la catégorie
        if ($product->getCategory()) {
            $categoryFolder = $this->getCategoryFolderName($product->getCategory()->getCode());
            $imagePath = $this->buildImagePath($categoryFolder, $fileName);
            if (file_exists($imagePath)) {
                return $imagePath;
            }
        }

        // Aucune image trouvée
        echo "Fichier image non trouvé pour le produit: {$product->getCode()} - fichier: {$fileName}\n";

        return null;
    }

    private function buildImagePath(string $folder, string $fileName): string
    {
        return '/workspace/moduscap/public/images/products/' . $folder . '/' . $fileName;
    }

    /**
     * Génère le texte alternatif pour l'image en tenant compte des langues disponibles
     */
    private function getAltTextForImage(Product $product, string $imageType, array $mediaConfig, ObjectManager $manager, int $galleryIndex = null): string
    {
        // Récupération des langues disponibles
        $languages = $manager->getRepository(Language::class)->findBy(['isActive' => true]);
        $defaultLocale = $this->getDefaultLocale($languages);
        
        // Définition des textes alternatifs par défaut si non configurés
        $defaultAltTexts = $this->getDefaultAltTexts($product, $imageType, $galleryIndex);
        
        // Vérification si des textes alternatifs sont configurés
        if (!isset($mediaConfig['alt_texts'])) {
            return $defaultAltTexts[$defaultLocale] ?? $defaultAltTexts['fr'] ?? $product->getName();
        }

        // Priorité à la langue par défaut, sinon première langue disponible
        $targetLocale = $defaultLocale;
        if (!isset($mediaConfig['alt_texts'][$targetLocale])) {
            $availableLocales = array_keys($mediaConfig['alt_texts']);
            $targetLocale = $availableLocales[0] ?? $defaultLocale;
        }

        // Récupération du texte alternatif dans la langue cible
        if ($imageType === 'main' && isset($mediaConfig['alt_texts'][$targetLocale]['main'])) {
            return $mediaConfig['alt_texts'][$targetLocale]['main'];
        }
        
        if ($imageType === 'gallery' && isset($mediaConfig['alt_texts'][$targetLocale]['gallery'][$galleryIndex])) {
            return $mediaConfig['alt_texts'][$targetLocale]['gallery'][$galleryIndex];
        }

        // Fallback vers le texte par défaut
        return $defaultAltTexts[$targetLocale] ?? $defaultAltTexts['fr'] ?? $product->getName();
    }

    private function getDefaultLocale(array $languages): string
    {
        foreach ($languages as $language) {
            if ($language->getIsDefault()) {
                return $language->getLocale();
            }
        }
        return 'fr'; // Fallback vers le français
    }

    private function getDefaultAltTexts(Product $product, string $imageType, ?int $galleryIndex): array
    {
        $productName = $product->getName();
        
        return [
            'fr' => $imageType === 'main' 
                ? "{$productName} - Image principale"
                : "{$productName} - Galerie" . ($galleryIndex !== null ? " " . ($galleryIndex + 1) : ""),
            'en' => $imageType === 'main' 
                ? "{$productName} - Main image"
                : "{$productName} - Gallery" . ($galleryIndex !== null ? " " . ($galleryIndex + 1) : "")
        ];
    }

    private function createMedia(
        ObjectManager $manager, 
        Product $product, 
        string $fileName, 
        string $mediaType, 
        bool $isMainImage, 
        int $sortOrder, 
        string $altText,
        string $imagePath
    ): void {
        try {
            // Vérification supplémentaire de l'existence du fichier
            if (!file_exists($imagePath)) {
                echo "Fichier image inexistant lors de la création: {$imagePath} - fichier: {$fileName}\n";
                return;
            }

            // Création de l'entité Media
            $media = new Media();
            $media->setFileName($fileName);
            $media->setOriginalFilename($fileName);
            $media->setExtension($this->getFileExtension($fileName));
            $media->setMimeType($this->getMimeType($fileName));
            $media->setFileSize(filesize($imagePath));
            $media->setAltText($altText);
            $media->setPath('uploads/media/' . $fileName);
            $media->setIsActive(true);

            $manager->persist($media);

            // Création de la relation ProductMedia
            $productMedia = new ProductMedia();
            $productMedia->setProduct($product);
            $productMedia->setMedia($media);
            $productMedia->setMediaType($mediaType);
            $productMedia->setIsMainImage($isMainImage);
            $productMedia->setSortOrder($sortOrder);

            $manager->persist($productMedia);

            echo "Média créé avec succès: {$product->getCode()} - {$fileName} ({$mediaType})\n";

        } catch (\Exception $e) {
            echo "Erreur lors de la création du média pour {$product->getCode()} - {$fileName}: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    private function getCategoryFolderName(?string $categoryCode): string
    {
        return self::FOLDER_MAPPINGS[$categoryCode] ?? 'otherpic';
    }

    private function getMimeType(string $fileName): string
    {
        $extension = $this->getFileExtension($fileName);
        return self::MIME_TYPES[strtolower($extension)] ?? 'image/jpeg';
    }

    private function getFileExtension(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }
}