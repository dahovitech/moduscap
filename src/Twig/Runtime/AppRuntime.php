<?php

namespace App\Twig\Runtime;

use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AppRuntime implements RuntimeExtensionInterface
{
    public function __construct(private EntityManagerInterface $entity_manager, private LanguageRepository $languageRepository)
    {
        // Inject dependencies if needed
    }

    public function getLanguages()
    {
        return $this->languageRepository->findActiveLanguages();
    }
}
