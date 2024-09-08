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
