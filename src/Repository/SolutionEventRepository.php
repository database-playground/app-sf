<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Group;
use App\Entity\Question;
use App\Entity\SolutionEvent;
use App\Entity\SolutionEventStatus;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SolutionEvent>
 */
class SolutionEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SolutionEvent::class);
    }

    /**
     * List all the questions this user has solved.
     *
     * @param User $user the user to query
     *
     * @return Question[] the questions that the user has solved
     */
    public function findSolvedQuestions(User $user): array
    {
        $solvedQuestionEvents = $this->findBy([
            'submitter' => $user,
            'status' => SolutionEventStatus::Passed,
        ]);

        $questions = [];

        foreach ($solvedQuestionEvents as $event) {
            $question = $event->getQuestion();

            if (!isset($questions[$question->getId()])) {
                $questions[$question->getId()] = $question;
            }
        }

        return array_values($questions);
    }

    /**
     * List all the solution events of a user.
     *
     * @param User                $user     The user to query
     * @param array<string,mixed> $criteria Additional criteria to filter the events
     * @param null|int            $limit    The maximum number of events to return
     * @param null|int            $offset   The offset of the events
     *
     * @return SolutionEvent[]
     */
    public function findUserEvents(User $user, array $criteria = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->findBy(
            array_merge([
                'submitter' => $user,
            ], $criteria),
            orderBy: [
                'id' => 'DESC',
            ],
            limit: $limit,
            offset: $offset,
        );
    }

    /**
     * List the solution events of a user for a specific question.
     *
     * @param Question $question The question to query
     * @param User     $user     The user to query
     * @param null|int $limit    The maximum number of events to return
     * @param null|int $offset   The offset of the events
     *
     * @return SolutionEvent[]
     */
    public function findUserQuestionEvents(Question $question, User $user, ?int $limit = null, ?int $offset = null): array
    {
        return $this->findBy(
            [
                'submitter' => $user,
                'question' => $question,
            ],
            orderBy: [
                'id' => 'DESC',
            ],
            limit: $limit,
            offset: $offset,
        );
    }

    /**
     * Get the question solve state.
     *
     * If the user has solved the question, it always returns `Passed`.
     * If the user has solved the question but failed, it returns `Failed`
     * If the user has not solved the question, it returns `null`.
     *
     * @param Question $question The question to check
     * @param User     $user     The user to check
     *
     * @return null|SolutionEventStatus The solve state, or `null` if the user has not solved the question
     */
    public function getSolveState(Question $question, User $user): ?SolutionEventStatus
    {
        // check if this user has ever passed
        $passed = $this->count([
            'submitter' => $user,
            'question' => $question,
            'status' => SolutionEventStatus::Passed,
        ]) > 0;
        if ($passed) {
            return SolutionEventStatus::Passed;
        }

        // check if this user has ever failed
        $failed = $this->count([
            'submitter' => $user,
            'question' => $question,
            'status' => SolutionEventStatus::Failed,
        ]) > 0;
        if ($failed) {
            return SolutionEventStatus::Failed;
        }

        return null;
    }

    /**
     * List the users leaderboard by the number of questions they have solved.
     *
     * @param null|Group $group    the group to filter the attempts by (null = no group)
     * @param string     $interval The interval to count the leaderboard
     *
     * @return list<array{user: User, count: int}> The leaderboard
     */
    public function listLeaderboard(?Group $group, string $interval): array
    {
        $startedFrom = new \DateTimeImmutable("-{$interval}");

        $qb = $this->createQueryBuilder('e')
            ->from(User::class, 'u')
            ->select('u AS user', 'COUNT(DISTINCT e.question) AS count')
            ->where('e.submitter = u')
            ->andWhere('e.status = :status')
            ->andWhere('e.createdAt >= :startedFrom')
            ->groupBy('u.id')
            ->orderBy('count', 'DESC')
            ->setParameter('status', SolutionEventStatus::Passed)
            ->setParameter('startedFrom', $startedFrom)
        ;

        // filter by group
        if (null !== $group) {
            $qb = $qb->andWhere('u.group = :group')
                ->setParameter('group', $group)
            ;
        } else {
            $qb = $qb->andWhere('u.group IS NULL');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get the total attempts made on the question.
     *
     * @param Question   $question the question to query
     * @param null|Group $group    the group to filter the attempts by (null = no group)
     *
     * @return SolutionEvent[] the total attempts made on the question
     */
    public function getTotalAttempts(Question $question, ?Group $group): array
    {
        $qb = $this->createQueryBuilder('se')
            ->join('se.submitter', 'submitter')
            ->where('se.question = :question')
            ->setParameter('question', $question)
        ;

        if (null !== $group) {
            $qb->andWhere('submitter.group = :group')
                ->setParameter('group', $group)
            ;
        } else {
            $qb->andWhere('submitter.group IS NULL');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get the latest query of a user for a question.
     *
     * @param Question $question  The question to query
     * @param User     $submitter The user to query
     *
     * @return null|SolutionEvent The latest query of the user for the question
     */
    public function getLatestQuery(Question $question, User $submitter): ?SolutionEvent
    {
        return $this->findOneBy([
            'question' => $question,
            'submitter' => $submitter,
        ], orderBy: ['id' => 'DESC']);
    }
}
