<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Question;
use App\Entity\User;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ChallengeController extends AbstractController
{
    /**
     * @return MockComment[]
     */
    private static function getMockComments(): array
    {
        return [
            new MockComment(0, 'Alice', 'This is a great question!', new \DateTime('now - 1 hour')),
            new MockComment(1, 'Bob', 'I agree with Alice.', new \DateTime('now - 30 minutes')),
            new MockComment(2, 'Charlie', 'I disagree with Bob.', new \DateTime('now')),
        ];
    }

    #[Route('/challenge/{id}', name: 'app_challenge')]
    public function index(
        #[CurrentUser] User $user,
        Question $question,
        QuestionRepository $questionRepository,
    ): Response {
        return $this->render('challenge/index.html.twig', [
            'user' => $user,
            'question' => $question,
            'limit' => $questionRepository->count(),
        ]);
    }

    #[Route('/challenge/{id}/comment', name: 'app_challenge_comment')]
    public function comment(#[CurrentUser] User $user): Response
    {
        return $this->render('challenge/comment.html.twig', [
            'user' => $user,
            'comments' => self::getMockComments(),
        ]);
    }

    #[Route('/challenge/{id}/comment/{comment_id}', name: 'app_challenge_comment_new')]
    public function newComment(#[CurrentUser] User $user, string $comment_id): Response
    {
        if (!\array_key_exists($comment_id, self::getMockComments())) {
            throw $this->createNotFoundException('The comment does not exist.');
        }

        return $this->render('challenge/new_comment.html.twig', [
            'user' => $user,
        ]);
    }
}

readonly class MockComment
{
    public function __construct(
        public int $id,
        public string $author,
        public string $content,
        public \DateTime $time)
    {
    }
}
