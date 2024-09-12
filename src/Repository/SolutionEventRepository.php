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
        $solvedQuestionEvents = $this->findBy([
            'submitter' => $user,
            'status' => SolutionEventStatus::Passed,
        ]);

        $questions = [];

        foreach ($solvedQuestionEvents as $event) {
            $question = $event->getQuestion();

            if (null !== $question && !isset($questions[$question->getId()])) {
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
}
