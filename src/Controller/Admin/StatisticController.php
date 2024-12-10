<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SolutionEventStatus;
use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use App\Service\PointCalculationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StatisticController extends AbstractController
{
    #[Route('/admin/statistic/last-login-at', name: 'admin_statistic_last_login_at')]
    public function lastLoginAt(UserRepository $userRepository): Response
    {
        /**
         * @var list<array{id: int, email: string, last_login_at: string|null}> $results
         */
        $results = $userRepository->createQueryBuilder('user')
            ->leftJoin('user.loginEvents', 'loginEvent')
            ->select(
                'user.id',
                'user.email',
                'MAX(loginEvent.createdAt) AS last_login_at',
            )
            ->groupBy('user.id', 'user.email')
            ->orderBy('last_login_at', 'DESC')
            ->getQuery()
            ->getResult();

        /**
         * @var list<array{id: int, email: string, last_login_at: string, recency: string}> $resultsWithRecency
         */
        $resultsWithRecency = [];
        /**
         * @var list<array{id: int, email: string}> $resultsThatNeverLogin
         */
        $resultsThatNeverLogin = [];

        foreach ($results as $result) {
            $lastLoginAt = ($lastLoginAt = $result['last_login_at']) !== null
                ? new \DateTime($lastLoginAt)
                : null;

            if (null !== $lastLoginAt) {
                $lastLoginAtString = $lastLoginAt->format('Y-m-d H:i:s');
                $recency = $lastLoginAt->diff(new \DateTime())->format('%a 天');

                $resultsWithRecency[] = [
                    'id' => $result['id'],
                    'email' => $result['email'],
                    'last_login_at' => $lastLoginAtString,
                    'recency' => $recency,
                ];
            } else {
                $resultsThatNeverLogin[] = [
                    'id' => $result['id'],
                    'email' => $result['email'],
                ];
            }
        }

        return $this->render('admin/statistics/last_login_at.html.twig', [
            'results' => $resultsWithRecency + $resultsThatNeverLogin,
        ]);
    }

    #[Route('/admin/statistic/completed-questions', name: 'admin_statistic_completed_questions')]
    public function completedQuestions(UserRepository $userRepository, QuestionRepository $questionRepository): Response
    {
        $totalQuestions = $questionRepository->count();

        /**
         * @var list<array{id: int, email: string, solved_questions: int<0, max>}> $userSolvedQuestionsCount
         */
        $userSolvedQuestionsCount = $userRepository->createQueryBuilder('u')
            ->select('u.id', 'u.email', 'COUNT(DISTINCT q) as solved_questions')
            ->leftJoin('u.solutionEvents', 'se')
            ->leftJoin('se.question', 'q')
            ->where('se.status = :status or se is NULL')
            ->groupBy('u.id', 'u.email')
            ->orderBy('solved_questions', 'DESC')
            ->setParameter('status', SolutionEventStatus::Passed)
            ->getQuery()
            ->getResult();

        return $this->render('admin/statistics/completed_questions.html.twig', [
            'totalQuestions' => $totalQuestions,
            'userSolvedQuestionsCount' => $userSolvedQuestionsCount,
        ]);
    }

    #[Route('/admin/statistic/experience-points', name: 'admin_statistic_experience_points')]
    public function experiencePoint(PointCalculationService $pointCalculationService, UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        /**
         * @var list<array{id: int, email: string, points: int}> $usersWithPoints
         */
        $usersWithPoints = [];

        foreach ($users as $user) {
            $point = $pointCalculationService->calculate($user);

            $usersWithPoints[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'points' => $point,
            ];
        }

        usort($usersWithPoints, fn (array $a, array $b) => $b['points'] <=> $a['points']);

        return $this->render('admin/statistics/experience_points.html.twig', [
            'usersWithPoints' => $usersWithPoints,
        ]);
    }
}
