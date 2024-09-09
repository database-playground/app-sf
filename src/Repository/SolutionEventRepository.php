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
     * List all the questions for a user with the specified status.
     *
     * @param User                $user   the user to get
     * @param SolutionEventStatus $status the status to filter
     *
     * @return Question[] the questions that the user has solved
     */
    public function listQuestionsWithStatus(User $user, SolutionEventStatus $status): array
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
            ->setParameter('status', $status)
            ->getQuery();

        /**
         * @var Question[] $result
         */
        $result = $q->getResult();

        return $result;
    }

    /**
     * List all the questions submit events for a user with the specified status.
     *
     * @return SolutionEvent[]
     */
    public function listAllEventsOfUser(User $user): array
    {
        return $this->findBy([
            'submitter' => $user,
        ], orderBy: [
            'id' => 'DESC',
        ]);
    }

    /**
     * List all solution events for a user on a question.
     *
     * @return SolutionEvent[] An array of SolutionEvent objects
     */
    public function listSolutionEvents(Question $question, User $user): array
    {
        return $this->findBy([
            'submitter' => $user,
            'question' => $question,
        ], orderBy: [
            'id' => 'DESC',
        ]);
    }

    /**
     * Get the latest solution state, ordering by PASSED, FAILED, then others.
     *
     * @param Question $question The question to check
     * @param User     $user     The user to check
     *
     * @return SolutionEventStatus|null The latest solution event. `null` if not solved yet.
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
