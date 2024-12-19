<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Meilisearch\Bundle\SearchService;
use Psr\Log\LoggerInterface;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Question::class)]
final readonly class QuestionReindexSubscriber
{
    public function __construct(
        private QuestionRepository $questionRepository,
        private SearchService $searchService,
        private LoggerInterface $logger,
    ) {}

    public function postUpdate(Question $question, PostUpdateEventArgs $event): void
    {
        $this->logger->info("Reindexing question since question #{$question->getId()} has been updated.");
        $this->questionRepository->reindex($this->searchService);
    }
}
