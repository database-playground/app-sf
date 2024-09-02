<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExercisesController extends AbstractController
{
    #[Route('/exercises', name: 'app_exercises')]
    public function index(): Response
    {
        return $this->render('exercises/index.html.twig');
    }
}
