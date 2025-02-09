<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\User;
use App\Form\FeedbackFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class FeedbackController extends AbstractController
{
    #[Route('/feedback', name: 'app_feedback')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser]
        ?User $user,
        #[MapQueryParameter]
        string $url,
        #[MapQueryParameter]
        ?string $description = null,
    ): Response {
        $feedback = (new Feedback())
            ->setSender($user)
            ->setMetadata([
                'url' => $url,
            ])
        ;

        if (null !== $description) {
            $feedback->setDescription($description);
        }

        $form = $this->createForm(FeedbackFormType::class, $feedback);
        $form = $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // add more metadata that does not affect by requests (e.g. user agent)
            // if we don't know the user, we store the IP address so we can track it.
            $feedback->setMetadata(array_merge(
                $feedback->getMetadata(),
                [
                    'user_agent' => $request->headers->get('User-Agent'),
                    'user' => $user?->getUserIdentifier(),
                    'ip' => null === $user ? $request->getClientIp() : null,
                ],
            ));

            $entityManager->persist($feedback);
            $entityManager->flush();

            return $this->render('feedback/index.html.twig', [
                'form' => null,
            ]);
        }

        return $this->render('feedback/index.html.twig', [
            'form' => $form,
        ]);
    }
}
