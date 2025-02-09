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
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/comments', name: 'app_comments')]
class CommentsController extends AbstractController
{
    /**
     * The primary color.
     *
     * You should get it from `app.scss`.
     */
    private const string primaryColor = '#4154f1';

    #[Route('/', name: '')]
    public function index(#[CurrentUser] User $user, CommentRepository $commentRepository): Response
    {
        if (!$this->isCommentFeatureEnabled()) {
            throw $this->createNotFoundException('"Comment" feature is disabled.');
        }

        $comments = $commentRepository->findUserComments($user);
        $likes = array_reduce($comments, static fn (int $carry, Comment $comment) => $carry + $comment->getCommentLikeEvents()->count(), 0);

        return $this->render('comments/index.html.twig', [
            'comments' => $comments,
            'likes' => $likes,
        ]);
    }

    /**
     * The bar chart of the like count.
     */
    #[Route('/cards/likes', name: '_likes')]
    public function likes(
        #[CurrentUser]
        User $user,
        CommentRepository $commentRepository,
        ChartBuilderInterface $chartBuilder,
        TranslatorInterface $translator,
    ): Response {
        if (!$this->isCommentFeatureEnabled()) {
            throw $this->createNotFoundException('"Comment" feature is disabled.');
        }

        $q = $commentRepository->createQueryBuilder('c')
            ->select('c.id AS id, COUNT(cle.id) AS count')
            ->leftJoin('c.commentLikeEvents', 'cle')
            ->where('c.commenter = :user')
            ->groupBy('c.id')
            ->orderBy('c.id')
            ->setParameter('user', $user)
            ->getQuery()
        ;

        /**
         * @var array<array{id: int, count: int}> $likesOfEachComment
         */
        $likesOfEachComment = $q->getResult();

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => array_map(static fn ($comment) => "#{$comment['id']}", $likesOfEachComment),
            'datasets' => [
                [
                    'label' => $translator->trans('charts.likes_of_each_comment'),
                    'backgroundColor' => self::primaryColor,
                    'data' => array_map(static fn ($comment) => $comment['count'], $likesOfEachComment),
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'ticks' => [
                        'beginAtZero' => true,
                        'min' => 0,
                        'stepSize' => 5,
                    ],
                ],
            ],
        ]);

        return $this->render('comments/likes.html.twig', [
            'chart' => $chart,
        ]);
    }

    private function isCommentFeatureEnabled(): bool
    {
        return $this->getParameter('app.features.comment');
    }
}
