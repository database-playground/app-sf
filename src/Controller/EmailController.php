<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\EmailDeliveryEvent;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EmailController extends AbstractController
{
    #[Route('/email/template/{name}', name: 'app_email_template')]
    #[IsGranted('ROLE_ADMIN')]
    public function templatePreview(string $name, Request $request): Response
    {
        $parameters = $request->query->all();

        return $this->render("email/mjml/$name.mjml.twig", $parameters);
    }

    #[Route('/email/{event}', name: 'app_email_preview')]
    public function details(#[CurrentUser] User $user, EmailDeliveryEvent $event): Response
    {
        // if this email is not owned by the current user and the user is not an admin,
        // we deny the access.
        if ($event->getToUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You are not authorized to access this email.');
        }

        return $this->render('email/preview.html.twig', [
            'emailDeliveryEvent' => $event,
        ]);
    }
}
