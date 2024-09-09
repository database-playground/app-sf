<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PointCalculationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OverviewController extends AbstractController
{
    #[Route('/overview', name: 'app_overview')]
    public function index(
        PointCalculationService $pointCalculationService,
    ): Response {
        return $this->render('overview/index.html.twig', [
            'points' => $pointCalculationService->calculate(),
        ]);
    }
}
