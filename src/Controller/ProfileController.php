<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Form\PasswordChangeModel;
use App\Entity\User;
use App\Form\NameChangeFormType;
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
    public function isProfileEditable(): bool
    {
        $isProfileEditable = $this->getParameter('app.features.editable-profile');
        \assert(\is_bool($isProfileEditable));

        return $isProfileEditable;
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(#[CurrentUser] User $user): Response
    {
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'isProfileEditable' => $this->isProfileEditable(),
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
        if (!$this->isProfileEditable()) {
            throw $this->createNotFoundException('Feature "Editable Profile" is disabled.');
        }

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

    #[Route('/profile/edit/username', name: 'app_profile_edit_username')]
    public function editUsername(
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        Request $request,
        #[CurrentUser] User $user,
    ): Response {
        if (!$this->isProfileEditable()) {
            throw $this->createNotFoundException('Feature "Editable Profile" is disabled.');
        }

        $usernameChangeForm = $formFactory->createBuilder(NameChangeFormType::class, $user)
            ->setAction($this->generateUrl('app_profile_edit_username'))
            ->getForm();
        $usernameChangeForm->handleRequest($request);
        $usernameUpdated = false;

        if ($usernameChangeForm->isSubmitted() && $usernameChangeForm->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $usernameUpdated = true;
        }

        return $this->render('profile/edit_username.html.twig', [
            'user' => $user,
            'username_change_form' => $usernameChangeForm,
            'username_updated' => $usernameUpdated,
        ]);
    }
}
