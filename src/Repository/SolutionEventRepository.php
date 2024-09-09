<?php

declare(strict_types=1);

namespace App\Repository;

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
    public function listSolvedQuestions(User $user): array
    {
        $q = $this->createQueryBuilder('solution_event')
            ->select('question.id')
            ->distinct()
            ->join('solution_event.question', 'question')
            ->where(
                'solution_event.submitter = :user',
                'solution_event.status = :status',
            )
            ->setParameter('user', $user)
            ->setParameter('status', SolutionEventStatus::Passed)
            ->getQuery();

        /**
         * @var Question[] $result
         */
        $result = $q->getResult();

        return $result;
    }

    /**
     * List all the solution events of a user.
     *
     * @param User                $user     The user to query
     * @param array<string,mixed> $criteria Additional criteria to filter the events
     * @param int|null            $limit    The maximum number of events to return
     *
     * @return SolutionEvent[]
     */
    public function listAllEvents(User $user, array $criteria = [], ?int $limit = null): array
    {
        return $this->findBy(
            array_merge([
                'submitter' => $user,
            ], $criteria),
            orderBy: [
                'id' => 'DESC',
            ],
            limit: $limit,
        );
    }

    /**
     * List the solution events of a user for a specific question.
     *
     * @return SolutionEvent[]
     */
    public function listSolvedEvents(Question $question, User $user): array
    {
        return $this->findBy([
            'submitter' => $user,
            'question' => $question,
        ], orderBy: [
            'id' => 'DESC',
        ]);
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
     * @return SolutionEventStatus|null The solve state, or `null` if the user has not solved the question
     */
    public function getSolveState(Question $question, User $user): ?SolutionEventStatus
    {
        $qb = $this->createQueryBuilder('solution_event')
            ->where('
                        solution_event.submitter = :user
                            AND solution_event.question = :question
                    ')
            ->orderBy("
                        case when solution_event.status = 'PASSED' then 1
                             when solution_event.status = 'FAILED' then 2
                             else 3 end
                    ")
            ->setMaxResults(1);

        /**
         * @var SolutionEvent[] $result
         */
        $result = $qb->getQuery()->execute([
            'user' => $user,
            'question' => $question,
        ]);
        if (empty($result)) {
            return null;
        }

        return $result[0]->getStatus();
    }
}
