<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Form\PasswordChangeModel;
use App\Entity\User;
use App\Form\PasswordChangeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(#[CurrentUser] User $user): Response
    {
        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit/password', name: 'app_profile_edit_password')]
    public function editPassword(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        Request $request,
        #[CurrentUser] User $user,
    ): Response {
        $passwordChangeModel = new PasswordChangeModel();
        $passwordChangeForm = $formFactory->createBuilder(PasswordChangeFormType::class, $passwordChangeModel)
            ->setAction($this->generateUrl('app_profile_edit_password'))
            ->getForm();
        $passwordChangeForm->handleRequest($request);
        $passwordUpdated = false;

        if ($passwordChangeForm->isSubmitted() && $passwordChangeForm->isValid()) {
            $hashedPassword = $passwordChangeModel->getHashedPassword($passwordHasher, $user);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $passwordUpdated = true;
        }

        return $this->render('profile/edit_password.html.twig', [
            'user' => $user,
            'password_change_form' => $passwordChangeForm,
            'password_updated' => $passwordUpdated,
        ]);
    }
}
