<?php

namespace App\Command;

use App\Entity\Language;
use App\Entity\Product;
use App\Entity\Media;
use App\Entity\ProductMedia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour charger les médias des produits
 */
#[AsCommand(
    name: 'app:load-product-media',
    description: 'Charge les médias (images) pour les produits existants',
)]
class LoadProductMediaCommand extends Command
{
    // Configuration des médias par produit
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
                'o_1j3qg2kpupkt4tprdk198d1g269_28910.jpg',
                'Natural.jpg@v=20251003.jpg',
                'A1_left side.jpg@v=20251003.jpg',
                'D1_right side.jpg@v=20251003.jpg',
                'E8_left side.jpg@v=20251003.jpg'
            ],
            'alt_texts' => [
                'fr' => [
                    'main' => 'Maison naturelle écologique - Vue principale',
                    'gallery' => [
                        'Matériaux naturels de la maison',
                        'Vue d\'ensemble de l\'architecture',
                        'Détails de construction écologique',
                        'Modèle Natural - Vue principale',
                        'Vue côté gauche modèle A1',
                        'Vue côté droit modèle D1',
                        'Vue côté gauche modèle E8'
                    ]
                ],
                'en' => [
                    'main' => 'Ecological natural house - Main view',
                    'gallery' => [
                        'Natural materials of the house',
                        'Overview of the architecture',
                        'Ecological construction details',
                        'Natural model - Main view',
                        'Left side view model A1',
                        'Right side view model D1',
                        'Left side view model E8'
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
        'catalogues' => 'catalogues',
    ];

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🖼️  Chargement des médias produits MODUSCAP');

        try {
            $this->createProductMedia($io);
            $this->entityManager->flush();
            
            $io->success('✅ Médias chargés avec succès !');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors du chargement des médias: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function createProductMedia(SymfonyStyle $io): void
    {
        $io->section('📊 Vérification des produits existants...');

        // Récupération des produits existants
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        
        if (empty($products)) {
            $io->warning('⚠️  Aucun produit trouvé pour créer les médias');
            $io->note('💡 Exécutez d\'abord: php bin/console app:load-products');
            return;
        }

        $io->info(sprintf('📦 %d produits trouvés pour l\'association des médias', count($products)));

        $totalMediaCreated = 0;
        $io->progressStart(count($products));

        // Traitement des produits par batch pour optimiser les performances
        foreach ($products as $product) {
            try {
                $mediaCount = $this->createProductImages($io, $product);
                $totalMediaCreated += $mediaCount;
                $io->progressAdvance();
            } catch (\Exception $e) {
                $io->errorText('❌ Erreur pour le produit: ' . $product->getCode() . ' - ' . $e->getMessage());
            }
        }
        $io->progressFinish();

        $io->info(sprintf('✅ %d médias créés au total pour %d produits', $totalMediaCreated, count($products)));
    }

    private function createProductImages(SymfonyStyle $io, Product $product): int
    {
        $productCode = $product->getCode();
        $mediaCount = 0;
        
        // Vérification de la configuration pour ce produit
        if (!isset(self::MEDIA_CONFIG[$productCode])) {
            $io->note("⚠️  Aucun média configuré pour le produit: {$productCode}");
            return 0;
        }

        $mediaConfig = self::MEDIA_CONFIG[$productCode];

        // Création de l'image principale
        if (isset($mediaConfig['main'])) {
            $mainImagePath = $this->validateImagePath($product, $mediaConfig['main']);
            if ($mainImagePath) {
                $altText = $this->getAltTextForImage($product, 'main', $mediaConfig);
                $this->createMedia($product, $mediaConfig['main'], 'main_image', true, 0, $altText, $mainImagePath);
                $mediaCount++;
                $io->note("✅ Image principale: {$productCode}");
            }
        }

        // Création des images de galerie
        if (isset($mediaConfig['gallery']) && is_array($mediaConfig['gallery'])) {
            $sortOrder = 1;
            foreach ($mediaConfig['gallery'] as $index => $galleryImage) {
                $galleryImagePath = $this->validateImagePath($product, $galleryImage);
                if ($galleryImagePath) {
                    $altText = $this->getAltTextForImage($product, 'gallery', $mediaConfig, $index);
                    $this->createMedia($product, $galleryImage, 'gallery', false, $sortOrder, $altText, $galleryImagePath);
                    $mediaCount++;
                    $sortOrder++;
                }
            }
            if ($mediaConfig['gallery']) {
                $io->note("✅ Galeries: {$productCode} ({$sortOrder - 1} images)");
            }
        }

        return $mediaCount;
    }

    /**
     * Valide l'existence du fichier image et retourne le chemin complet en essayant différents dossiers
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

        return null;
    }

    /**
     * Génère le texte alternatif pour l'image en tenant compte des langues disponibles
     */
    private function getAltTextForImage(Product $product, string $imageType, array $mediaConfig, int $galleryIndex = null): string
    {
        // Récupération des langues disponibles
        $languages = $this->entityManager->getRepository(Language::class)->findBy(['isActive' => true]);
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
        Product $product, 
        string $fileName, 
        string $mediaType, 
        bool $isMainImage, 
        int $sortOrder, 
        string $altText,
        string $imagePath
    ): void {
        // Vérification supplémentaire de l'existence du fichier
        if (!file_exists($imagePath)) {
            throw new \Exception("Fichier image inexistant: {$imagePath}");
        }

        // Création de l'entité Media
        $media = new Media();
        $media->setFileName($fileName);
        $media->setAlt($altText);
        $media->setExtension($this->getFileExtension($fileName));

        $this->entityManager->persist($media);

        // Création de la relation ProductMedia
        $productMedia = new ProductMedia();
        $productMedia->setProduct($product);
        $productMedia->setMedia($media);
        $productMedia->setMediaType($mediaType);
        $productMedia->setIsMainImage($isMainImage);
        $productMedia->setSortOrder($sortOrder);

        $this->entityManager->persist($productMedia);
    }

    private function getCategoryFolderName(?string $categoryCode): string
    {
        return self::FOLDER_MAPPINGS[$categoryCode] ?? 'otherpic';
    }

    private function getFileExtension(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }

    /**
     * Construit le chemin complet vers un fichier image
     */
    private function buildImagePath(string $folderName, string $fileName): string
    {
        return sprintf(
            '%s/public/images/products/%s/%s',
            $this->getProjectRoot(),
            $folderName,
            $fileName
        );
    }

    /**
     * Récupère le chemin racine du projet
     */
    private function getProjectRoot(): string
    {
        return dirname(__DIR__, 2); // Remonte 2 niveaux depuis src/Command
    }
}