<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\CommentLikeEvent;
use App\Entity\Group;
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
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('User management');
        yield MenuItem::linkToCrud('Users', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Groups', 'fa fa-group', Group::class);

        yield MenuItem::section('Question management');
        yield MenuItem::linkToCrud('Schema', 'fa fa-database', Schema::class);
        yield MenuItem::linkToCrud('Questions', 'fa fa-question', Question::class);

        yield MenuItem::section('Comments');
        yield MenuItem::linkToCrud('Comments', 'fa fa-comment', Comment::class);

        yield MenuItem::section('Events');
        yield MenuItem::linkToCrud('Solution Events', 'fa fa-check', SolutionEvent::class);
        yield MenuItem::linkToCrud('Solution Video Events', 'fa fa-video', SolutionVideoEvent::class);
        yield MenuItem::linkToCrud('Comments Events', 'fa fa-comment', CommentLikeEvent::class);
    }
}
