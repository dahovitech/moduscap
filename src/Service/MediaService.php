<?php

namespace App\Service;

use App\Entity\Media;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Service pour la gestion des médias
 */
class MediaService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml'
    ];
    
    private const MAX_FILE_SIZE = 10485760; // 10MB
    
    public function __construct(
        private MediaRepository $mediaRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Upload et création d'un nouveau média
     */
    public function uploadMedia(UploadedFile $file, ?string $alt = null): Media
    {
        $this->validateFile($file);
        
        $media = new Media();
        $media->setFile($file);
        if ($alt) {
            $media->setAlt($alt);
        }
        
        $this->entityManager->persist($media);
        $this->entityManager->flush();
        
        return $media;
    }

    /**
     * Obtenir tous les médias avec pagination
     */
    public function getMediaList(int $page = 1, int $limit = 20, ?string $search = null): array
    {
        $offset = ($page - 1) * $limit;
        
        if ($search) {
            $medias = $this->mediaRepository->findBySearch($search, $limit, $offset);
            $total = $this->mediaRepository->countBySearch($search);
        } else {
            $medias = $this->mediaRepository->findBy([], ['id' => 'DESC'], $limit, $offset);
            $total = $this->mediaRepository->count([]);
        }
        
        return [
            'medias' => $medias,
            'total' => $total,
            'totalPages' => ceil($total / $limit),
            'currentPage' => $page
        ];
    }

    /**
     * Obtenir un média par ID
     */
    public function getMediaById(int $id): ?Media
    {
        return $this->mediaRepository->find($id);
    }

    /**
     * Supprimer un média
     */
    public function deleteMedia(Media $media): void
    {
        // Supprimer le fichier physique
        $filePath = $media->getUploadRootDir() . DIRECTORY_SEPARATOR . $media->getFileName();
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $this->entityManager->remove($media);
        $this->entityManager->flush();
    }

    /**
     * Valider un fichier uploadé
     */
    private function validateFile(UploadedFile $file): void
    {
        // Vérifier la taille
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new FileException('Le fichier est trop volumineux. Taille maximum autorisée : 10MB');
        }
        
        // Vérifier le type MIME
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new FileException('Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WebP, SVG');
        }
        
        // Vérifier que c'est bien une image (sauf pour SVG)
        if ($file->getMimeType() !== 'image/svg+xml' && !getimagesize($file->getPathname())) {
            throw new FileException('Le fichier uploadé n\'est pas une image valide');
        }
    }
}
