<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Question;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class PointCalculationService
{
    protected User $user;
    protected static int $BASE_SCORE = 500;

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function calculate(User $user): int
    {
        return self::$BASE_SCORE
            + $this->calculateSolutionQuestionPoints($user)
            + $this->calculateFirstSolutionPoints($user);
    }

    /**
     * Calculate the total points of the solution events.
     *
     * 每位同學基本經驗值500點，成功解一題獲得經驗值增加。易:10點、 中:20點、難:30點
     *
     * @param User $user The user to calculate the points for
     *
     * @return int The total points of the user
     */
    protected function calculateSolutionQuestionPoints(User $user): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb = $qb
            ->from(Question::class, 'q')
            ->leftJoin('q.solutionEvents', 'se')
            ->where(
                'se.submitter = :submitter',
                "se.status = 'PASSED'"
            )
            ->groupBy('se.question', 'se.submitter')
            ->select("MAX((
                case when q.difficulty = 'EASY' then 10
                when q.difficulty = 'MEDIUM' then 20
                when q.difficulty = 'HARD' then 30
                else 0
                end
            )) AS point")
            ->setParameter('submitter', $user);

        /**
         * @var array<array{point: int}> $result
         */
        $result = $qb->getQuery()->getResult();

        return array_reduce($result, fn (int $carry, array $row) => $carry + $row['point'], 0);
    }

    /**
     * Calculate the points if the user is the first solver of a question.
     *
     * 第一位解題成功者加10點。
     */
    protected function calculateFirstSolutionPoints(User $user): int
    {
        $nq = $this->entityManager->createNativeQuery("
            SELECT DISTINCT ON (question_id) submitter_id
            FROM solution_event
            WHERE status = 'PASSED'
            ORDER BY question_id, id;
        ",
            (new ResultSetMapping())
                ->addScalarResult(
                    'submitter_id',
                    'sid',
                    'integer'
                )
        );

        /**
         * @var array<array{sid: int}> $result
         */
        $result = $nq->getResult();

        return array_reduce($result, fn (int $carry, array $row) => $carry + ($row['sid'] === $user->getId() ? 10 : 0), 0);
    }
}
