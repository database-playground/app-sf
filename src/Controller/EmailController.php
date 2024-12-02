<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\EmailEvent;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class EmailController extends AbstractController
{
    #[Route('/email/{id}', name: 'app_email_preview')]
    public function details(#[CurrentUser] User $user, EmailEvent $emailEvent): Response
    {
        // if this email is not owned by the current user and the user is not an admin,
        // we deny the access.
        if ($emailEvent->getToUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You are not authorized to access this email.');
        }

        return $this->render('email/preview.html.twig', [
            'email' => $emailEvent,
        ]);
    }
}
