<?php

namespace App\Twig\Runtime;

use App\Entity\Language;
use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AppRuntime implements RuntimeExtensionInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        // Inject dependencies if needed
    }

    public function onLanguages()
    {
        return $this->entityManager->getRepository(Language::class)->findActiveLanguages();
    }


    public function onSetting($property, $isBoolean = false)
    {
        $settingRepository = $this->entityManager->getRepository(Setting::class);
        $setting = $settingRepository->findOneBy([]); // Assuming you want to get the first setting

        if (!$setting) {
            $setting = new Setting();
            $this->entityManager->persist($setting);
            $this->entityManager->flush();
            $setting = $settingRepository->findOneBy([]);
        }
        $method = $isBoolean ? 'is' . ucfirst($property) : 'get' . ucfirst($property);

        if (!method_exists($setting, $method)) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist in class "%s".', $method, get_class($setting)));
        }

        return $setting->$method() ? $setting->$method() : "";
    }
}
