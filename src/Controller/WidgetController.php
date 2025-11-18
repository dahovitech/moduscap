<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WidgetController extends AbstractController
{


    public function header () {
        return $this->render('@theme/widget/header.html.twig');
    }

    public function footer () {
        return $this->render('@theme/widget/footer.html.twig');
    }
}
