<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\ProductMedia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;

/**
 * Service de gestion avancée des médias et images de produits
 */
class MediaImageService
{
    private const MAX_MAIN_IMAGE_WIDTH = 1200;
    private const MAX_MAIN_IMAGE_HEIGHT = 800;
    private const MAX_GALLERY_IMAGE_WIDTH = 800;
    private const MAX_GALLERY_IMAGE_HEIGHT = 600;
    private const THUMBNAIL_SIZE = 300;
    private const QUALITY = 85;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Optimise automatiquement une image selon son type d'usage
     */
    public function optimizeImage(Media $media, string $mediaType = 'gallery'): bool
    {
        if (!$media->getFileName() || !file_exists($media->getAbsolutePath())) {
            return false;
        }

        $imagePath = $media->getAbsolutePath();
        $imagine = new Imagine();

        try {
            $image = $imagine->open($imagePath);
            $size = $image->getSize();
            
            // Définir les dimensions maximales selon le type
            switch ($mediaType) {
                case 'main_image':
                    $maxWidth = self::MAX_MAIN_IMAGE_WIDTH;
                    $maxHeight = self::MAX_MAIN_IMAGE_HEIGHT;
                    break;
                case 'gallery':
                case 'lifestyle':
                    $maxWidth = self::MAX_GALLERY_IMAGE_WIDTH;
                    $maxHeight = self::MAX_GALLERY_IMAGE_HEIGHT;
                    break;
                case 'technical':
                    // Les images techniques gardent leurs dimensions originales
                    return true;
                default:
                    $maxWidth = self::MAX_GALLERY_IMAGE_WIDTH;
                    $maxHeight = self::MAX_GALLERY_IMAGE_HEIGHT;
            }

            // Redimensionner si nécessaire
            if ($size->getWidth() > $maxWidth || $size->getHeight() > $maxHeight) {
                $ratio = min($maxWidth / $size->getWidth(), $maxHeight / $size->getHeight());
                $newWidth = (int)($size->getWidth() * $ratio);
                $newHeight = (int)($size->getHeight() * $ratio);

                $image->resize(new Box($newWidth, $newHeight));
            }

            // Sauvegarder avec optimisation
            $image->save($imagePath, [
                'quality' => self::QUALITY,
                'flatten' => true
            ]);

            return true;
        } catch (\Exception $e) {
            // Logger l'erreur en production
            return false;
        }
    }

    /**
     * Génère automatiquement des variantes d'images pour un produit
     */
    public function generateProductImageVariants(ProductMedia $productMedia): array
    {
        $media = $productMedia->getMedia();
        if (!$media || !$media->getFileName()) {
            return [];
        }

        $variants = [];
        $imagePath = $media->getAbsolutePath();
        
        if (!file_exists($imagePath)) {
            return $variants;
        }

        $imagine = new Imagine();

        try {
            $image = $imagine->open($imagePath);
            $originalSize = $image->getSize();
            
            // Générer différentes tailles selon le type
            switch ($productMedia->getMediaType()) {
                case 'main_image':
                    $variants = $this->generateMainImageVariants($image, $originalSize);
                    break;
                case 'gallery':
                case 'lifestyle':
                    $variants = $this->generateGalleryVariants($image, $originalSize);
                    break;
                case 'technical':
                    // Les images techniques ne nécessitent qu'un thumbnail
                    $variants = $this->generateThumbnailOnly($image, $originalSize);
                    break;
            }

            return $variants;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Génère les variantes pour image principale
     */
    private function generateMainImageVariants($image, $originalSize): array
    {
        $variants = [];
        
        // Image hero (grande)
        $heroSize = new Box(self::MAX_MAIN_IMAGE_WIDTH, self::MAX_MAIN_IMAGE_HEIGHT);
        if ($originalSize->getWidth() > $heroSize->getWidth() || $originalSize->getHeight() > $heroSize->getHeight()) {
            $heroImage = clone $image;
            $heroImage->resize($heroSize)->save($this->getVariantPath('hero'), ['quality' => self::QUALITY]);
            $variants['hero'] = $this->getVariantPath('hero');
        }

        // Image medium
        $mediumSize = new Box(800, 533);
        $mediumImage = clone $image;
        $mediumImage->resize($mediumSize)->save($this->getVariantPath('medium'), ['quality' => self::QUALITY]);
        $variants['medium'] = $this->getVariantPath('medium');

        // Thumbnail
        $thumbnailImage = clone $image;
        $thumbnailSize = new Box(self::THUMBNAIL_SIZE, self::THUMBNAIL_SIZE);
        $thumbnailImage->resize($thumbnailSize)->save($this->getVariantPath('thumb'), ['quality' => 75]);
        $variants['thumb'] = $this->getVariantPath('thumb');

        return $variants;
    }

    /**
     * Génère les variantes pour images de galerie
     */
    private function generateGalleryVariants($image, $originalSize): array
    {
        $variants = [];
        
        // Image medium
        $mediumSize = new Box(self::MAX_GALLERY_IMAGE_WIDTH, self::MAX_GALLERY_IMAGE_HEIGHT);
        if ($originalSize->getWidth() > $mediumSize->getWidth() || $originalSize->getHeight() > $mediumSize->getHeight()) {
            $mediumImage = clone $image;
            $mediumImage->resize($mediumSize)->save($this->getVariantPath('medium'), ['quality' => self::QUALITY]);
            $variants['medium'] = $this->getVariantPath('medium');
        }

        // Thumbnail
        $thumbnailImage = clone $image;
        $thumbnailSize = new Box(self::THUMBNAIL_SIZE, self::THUMBNAIL_SIZE);
        $thumbnailImage->resize($thumbnailSize)->save($this->getVariantPath('thumb'), ['quality' => 75]);
        $variants['thumb'] = $this->getVariantPath('thumb');

        return $variants;
    }

    /**
     * Génère uniquement un thumbnail pour images techniques
     */
    private function generateThumbnailOnly($image, $originalSize): array
    {
        $variants = [];
        
        // Thumbnail
        $thumbnailImage = clone $image;
        $thumbnailSize = new Box(self::THUMBNAIL_SIZE, self::THUMBNAIL_SIZE);
        $thumbnailImage->resize($thumbnailSize)->save($this->getVariantPath('thumb'), ['quality' => 75]);
        $variants['thumb'] = $this->getVariantPath('thumb');

        return $variants;
    }

    /**
     * Nettoie automatiquement les images orphelines
     */
    public function cleanupOrphanedImages(): int
    {
        $uploadDir = $this->getUploadRootDir();
        $mediaRepository = $this->entityManager->getRepository(Media::class);
        
        $orphanedCount = 0;
        
        // Parcourir tous les fichiers dans le dossier d'upload
        $files = glob($uploadDir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileName = basename($file);
                
                // Vérifier si le fichier existe dans la base de données
                $media = $mediaRepository->findOneBy(['fileName' => $fileName]);
                
                if (!$media) {
                    // Fichier orphelin - le supprimer
                    unlink($file);
                    $orphanedCount++;
                }
            }
        }
        
        return $orphanedCount;
    }

    /**
     * Optimise toutes les images d'un produit
     */
    public function optimizeProductImages(ProductMedia $productMedia): bool
    {
        return $this->optimizeImage($productMedia->getMedia(), $productMedia->getMediaType());
    }

    /**
     * Génère un chemin de variante d'image
     */
    private function getVariantPath(string $variant): string
    {
        return $this->getUploadRootDir() . '/variants/' . $variant . '/';
    }

    /**
     * Obtient le chemin absolu d'un média
     */
    public function getMediaAbsolutePath(Media $media): string
    {
        return $this->getUploadRootDir() . '/' . $media->getFileName();
    }

    /**
     * Obtient le dossier d'upload racine
     */
    private function getUploadRootDir(): string
    {
        return __DIR__ . '/../../public/uploads/media';
    }

    /**
     * Valide les dimensions d'une image
     */
    public function validateImageDimensions(UploadedFile $file, array $constraints = []): array
    {
        $imagine = new Imagine();
        $image = $imagine->open($file->getPathname());
        $size = $image->getSize();
        
        $errors = [];
        
        if (isset($constraints['min_width']) && $size->getWidth() < $constraints['min_width']) {
            $errors[] = sprintf('La largeur minimum est de %dpx', $constraints['min_width']);
        }
        
        if (isset($constraints['max_width']) && $size->getWidth() > $constraints['max_width']) {
            $errors[] = sprintf('La largeur maximum est de %dpx', $constraints['max_width']);
        }
        
        if (isset($constraints['min_height']) && $size->getHeight() < $constraints['min_height']) {
            $errors[] = sprintf('La hauteur minimum est de %dpx', $constraints['min_height']);
        }
        
        if (isset($constraints['max_height']) && $size->getHeight() > $constraints['max_height']) {
            $errors[] = sprintf('La hauteur maximum est de %dpx', $constraints['max_height']);
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'dimensions' => [
                'width' => $size->getWidth(),
                'height' => $size->getHeight()
            ]
        ];
    }
}