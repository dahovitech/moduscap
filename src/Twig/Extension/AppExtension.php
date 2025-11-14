<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('get_languages', [AppRuntime::class, 'onLanguages']),
            new TwigFilter('setting', [AppRuntime::class, 'onSetting']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_languages', [AppRuntime::class, 'onLanguages']),
            new TwigFunction('setting', [AppRuntime::class, 'onSetting']),
        ];
    }
}
