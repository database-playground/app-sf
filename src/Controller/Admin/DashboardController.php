<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\CommentLikeEvent;
use App\Entity\Group;
use App\Entity\HintOpenEvent;
use App\Entity\Question;
use App\Entity\Schema;
use App\Entity\SolutionEvent;
use App\Entity\SolutionVideoEvent;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Database Playground application');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Back to App', 'fa fa-arrow-left', 'app_home');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('User management');
        yield MenuItem::linkToCrud('User', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Group', 'fa fa-group', Group::class);

        yield MenuItem::section('Question management');
        yield MenuItem::linkToCrud('Schema', 'fa fa-database', Schema::class);
        yield MenuItem::linkToCrud('Questions', 'fa fa-question', Question::class);

        yield MenuItem::section('Comments');
        yield MenuItem::linkToCrud('Comment', 'fa fa-comment', Comment::class);
        yield MenuItem::linkToCrud('CommentLikeEvent', 'fa fa-thumbs-up', CommentLikeEvent::class);

        yield MenuItem::section('Events');
        yield MenuItem::linkToCrud('SolutionEvent', 'fa fa-check', SolutionEvent::class);
        yield MenuItem::linkToCrud('SolutionVideoEvent', 'fa fa-video', SolutionVideoEvent::class);
        yield MenuItem::linkToCrud('HintOpenEvent', 'fa fa-lightbulb', HintOpenEvent::class);
    }
}
