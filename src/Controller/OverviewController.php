<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\AnnouncementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OverviewController extends AbstractController
{
    #[Route('/overview', name: 'app_overview')]
    public function index(): Response
    {
        return $this->render('overview/index.html.twig');
    }

    #[Route('/overview/announcements', name: 'app_overview_announcements')]
    public function announcements(AnnouncementRepository $announcementRepository): Response
    {
        $announcements = $announcementRepository->findAllPublished();

        return $this->render('overview/announcements.html.twig', [
            'announcements' => $announcements,
        ]);
    }
}
