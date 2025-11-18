<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Repository\MediaRepository;
use App\Service\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/media', name: 'admin_media_')]
#[IsGranted('ROLE_ADMIN')]
class MediaController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
        private MediaService $mediaService
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(MediaRepository $mediaRepository): Response
    {
        $medias = $mediaRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
        ]);
    }

    #[Route('/upload', name: 'upload', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $files = $request->files->get('files');
        if (is_array($files)) {
            return $this->multiUpload($request, $entityManager);
        }

        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile instanceof UploadedFile) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->translator->trans('admin.errors.media.upload.no_file', [], 'admin')
            ], 400);
        }

        return $this->processFileUpload($uploadedFile, $entityManager);
    }

    #[Route('/multi-upload', name: 'multi_upload', methods: ['POST'])]
    public function multiUpload(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $uploadedFiles = $request->files->get('files');

        if (!is_array($uploadedFiles) || empty($uploadedFiles)) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->translator->trans('admin.errors.media.upload.no_file', [], 'admin')
            ], 400);
        }

        $results = [];
        $successCount = 0;
        $errors = [];

        foreach ($uploadedFiles as $index => $uploadedFile) {
            if (!$uploadedFile instanceof UploadedFile) {
                $errors[] = $this->translator->trans('admin.errors.media.upload.invalid_file', ['%index%' => $index + 1], 'admin');
                continue;
            }

            try {
                $result = $this->processFileUpload($uploadedFile, $entityManager, false);
                $data = json_decode($result->getContent(), true);

                if ($data['success']) {
                    $results[] = $data['media'];
                    $successCount++;
                } else {
                    $errors[] = $this->translator->trans('admin.errors.media.upload.file_error', [
                        '%file%' => $uploadedFile->getClientOriginalName(),
                        '%message%' => $data['message']
                    ]);
                }
            } catch (\Exception $e) {
                $errors[] = $this->translator->trans('admin.errors.media.upload.exception', [
                    '%file%' => $uploadedFile->getClientOriginalName(),
                    '%error%' => $e->getMessage()
                ]);
            }
        }

        $entityManager->flush();

        $message = $successCount > 0
            ? $this->translator->trans('admin.errors.media.upload.success_with_errors', [
                '%count%' => $successCount,
                '%errors%' => count($errors)
            ])
            : $this->translator->trans('admin.errors.media.upload.no_success', [], 'admin');

        return new JsonResponse([
            'success' => $successCount > 0,
            'message' => $message,
            'results' => $results,
            'errors' => $errors,
            'successCount' => $successCount,
            'totalCount' => count($uploadedFiles)
        ]);
    }

    private function processFileUpload(
        UploadedFile $uploadedFile,
        EntityManagerInterface $entityManager,
        bool $flush = true
    ): JsonResponse {
        $allowedMimeTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
            'image/svg+xml', 'application/pdf', 'video/mp4', 'video/webm',
            'audio/mpeg', 'audio/wav', 'audio/ogg'
        ];

        if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->translator->trans('admin.errors.media.upload.forbidden_type', [], 'admin')
            ], 400);
        }

        if ($uploadedFile->getSize() > 10485760) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->translator->trans('admin.errors.media.upload.too_large', [], 'admin')
            ], 400);
        }

        try {
            $media = new Media();
            $media->setFile($uploadedFile);
            $media->setAlt($uploadedFile->getClientOriginalName());

            $entityManager->persist($media);
            if ($flush) {
                $entityManager->flush();
            }

            return new JsonResponse([
                'success' => true,
                'media' => [
                    'id' => $media->getId(),
                    'fileName' => $media->getFileName(),
                    'alt' => $media->getAlt(),
                    'extension' => $media->getExtension(),
                    'webPath' => $media->getWebPath(),
                    'url' => '/' . $media->getWebPath()
                ]
            ]);
        } catch (FileException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->translator->trans('admin.errors.media.upload.file_exception', ['%error%' => $e->getMessage()], 'admin')
            ], 500);
        }
    }

    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request, MediaRepository $mediaRepository): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(50, max(10, $request->query->getInt('limit', 20)));
        $search = $request->query->get('search', '');
        $offset = ($page - 1) * $limit;

        if ($search) {
            $medias = $mediaRepository->createQueryBuilder('m')
                ->where('m.alt LIKE :search OR m.fileName LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->orderBy('m.id', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $total = $mediaRepository->createQueryBuilder('m')
                ->select('COUNT(m.id)')
                ->where('m.alt LIKE :search OR m.fileName LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->getQuery()
                ->getSingleScalarResult();
        } else {
            $medias = $mediaRepository->findBy([], ['id' => 'DESC'], $limit, $offset);
            $total = $mediaRepository->count([]);
        }

        $mediasData = array_map(fn(Media $media) => [
            'id' => $media->getId(),
            'fileName' => $media->getFileName(),
            'alt' => $media->getAlt(),
            'extension' => $media->getExtension(),
            'webPath' => $media->getWebPath(),
            'url' => '/' . $media->getWebPath(),
            'isImage' => in_array($media->getExtension(), ['jpg', 'jpeg', 'png', 'gif', 'webp'])
        ], $medias);

        return new JsonResponse([
            'medias' => $mediasData,
            'pagination' => [
                'current' => $page,
                'total' => (int) ceil($total / $limit),
                'count' => count($mediasData),
                'totalItems' => (int) $total
            ]
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['DELETE'])]
    public function delete(Media $media, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $entityManager->remove($media);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => $this->translator->trans('admin.errors.media.delete.success', [], 'admin')
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->translator->trans('admin.errors.media.delete.error', [], 'admin')
            ], 500);
        }
    }

    #[Route('/{id}/update', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Media $media, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['alt'])) {
            $media->setAlt($data['alt']);
        }

        try {
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'media' => [
                    'id' => $media->getId(),
                    'fileName' => $media->getFileName(),
                    'alt' => $media->getAlt(),
                    'extension' => $media->getExtension(),
                    'webPath' => $media->getWebPath(),
                    'url' => '/' . $media->getWebPath()
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->translator->trans('admin.errors.media.update.error', [], 'admin')
            ], 500);
        }
    }
}