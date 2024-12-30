<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\AnnouncementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class OverviewController extends AbstractController
{
    #[Route('/overview', name: 'app_overview')]
    public function index(#[CurrentUser] User $user): Response
    {
        $layoutName = $user->getGroup()?->getLayout() ?? 'default';
        $mainContent = $this->renderView("overview/layout/{$layoutName}.html.twig");

        return $this->render('overview/index.html.twig', [
            'mainContent' => $mainContent,
        ]);
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
