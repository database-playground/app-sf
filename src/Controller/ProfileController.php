<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Form\PasswordChangeModel;
use App\Entity\User;
use App\Form\PasswordChangeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        Request $request,
        #[CurrentUser] User $user,
    ): Response {
        $passwordChangeModel = new PasswordChangeModel();
        $passwordChangeForm = $this->createForm(PasswordChangeFormType::class, $passwordChangeModel);
        $passwordChangeForm->handleRequest($request);
        $passwordUpdated = false;

        if ($passwordChangeForm->isSubmitted() && $passwordChangeForm->isValid()) {
            $hashedPassword = $passwordChangeModel->getHashedPassword($passwordHasher, $user);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $passwordUpdated = true;
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'password_change_form' => $passwordChangeForm,
            'password_updated' => $passwordUpdated,
        ]);
    }
}
