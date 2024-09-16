<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class CommentsController extends AbstractController
{
    #[Route('/comments', name: 'app_comments')]
    public function index(#[CurrentUser] User $user, CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findUserComments($user);
        $likes = array_reduce($comments, fn (int $carry, Comment $comment) => $carry + $comment->getCommentLikeEvents()->count(), 0);

        return $this->render('comments/index.html.twig', [
            'comments' => $comments,
            'likes' => $likes,
        ]);
    }
}
