<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Announcement;
use App\Entity\Comment;
use App\Entity\CommentLikeEvent;
use App\Entity\Email;
use App\Entity\EmailDeliveryEvent;
use App\Entity\Feedback;
use App\Entity\Group;
use App\Entity\HintOpenEvent;
use App\Entity\LoginEvent;
use App\Entity\Question;
use App\Entity\Schema;
use App\Entity\SolutionEvent;
use App\Entity\SolutionVideoEvent;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->redirect(
            $this->adminUrlGenerator->setController(UserCrudController::class)->generateUrl()
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Database Playground application');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Back to App', 'fa fa-arrow-left', 'app_home');

        yield MenuItem::section('Statistics');
        yield MenuItem::linkToRoute('Last login at', 'fa fa-sign-in-alt', 'admin_statistic_last_login_at');
        yield MenuItem::linkToRoute('Completed Questions', 'fa fa-trophy', 'admin_statistic_completed_questions');

        yield MenuItem::section('User management');
        yield MenuItem::linkToCrud('User', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Group', 'fa fa-users', Group::class);

        yield MenuItem::section('Question management');
        yield MenuItem::linkToCrud('Schema', 'fa fa-database', Schema::class);
        yield MenuItem::linkToCrud('Questions', 'fa fa-question', Question::class);

        yield MenuItem::section('System Management');
        yield MenuItem::linkToCrud('Announcement', 'fa fa-bullhorn', Announcement::class);

        yield MenuItem::section('Comments');
        yield MenuItem::linkToCrud('Comment', 'fa fa-comment', Comment::class);
        yield MenuItem::linkToCrud('CommentLikeEvent', 'fa fa-thumbs-up', CommentLikeEvent::class);

        yield MenuItem::section('Mails');
        yield MenuItem::linkToRoute('EmailTemplates', 'fa fa-layer-group', 'admin_emailtemplate_index');
        yield MenuItem::linkToCrud('Email', 'fa fa-envelope', Email::class);
        yield MenuItem::linkToCrud('EmailDeliveryEvent', 'fa fa-paper-plane', EmailDeliveryEvent::class);

        yield MenuItem::section('Events');
        yield MenuItem::linkToCrud('SolutionEvent', 'fa fa-check', SolutionEvent::class);
        yield MenuItem::linkToCrud('SolutionVideoEvent', 'fa fa-video', SolutionVideoEvent::class);
        yield MenuItem::linkToCrud('HintOpenEvent', 'fa fa-lightbulb', HintOpenEvent::class);
        yield MenuItem::linkToCrud('LoginEvent', 'fa fa-right-to-bracket', LoginEvent::class);

        yield MenuItem::section('Feedback');
        yield MenuItem::linkToCrud('Feedback', 'fa fa-comments', Feedback::class);
    }
}
