<?php

declare(strict_types=1);

namespace App\Twig\Components\Questions;

use App\Entity\Question;
use App\Entity\User;
use App\Repository\QuestionRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;

#[AsLiveComponent]
final class FilterableSection
{
    use DefaultActionTrait;

    public string $title = '題庫一覽';

    #[LiveProp]
    public User $currentUser;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'))]
    public string $query = '';

    #[LiveProp(writable: true, url: new UrlMapping(as: 'p'))]
    private int $currentPage = 1;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'type'))]
    public string $type = '';

    public function __construct(
        private readonly QuestionRepository $questionRepository,
    ) {
    }

    /**
     * List the questions based on the current query and type.
     *
     * @return Question[] The list of questions
     */
    public function getQuestions(): array
    {
        return $this->questionRepository->search(
            query: $this->query,
            type: $this->type,
            page: $this->getCurrentPage(),
        );
    }

    /**
     * Get all question types.
     *
     * @return array<string> the list of question types
     */
    public function getTypesList(): array
    {
        return $this->questionRepository->listTypes();
    }

    /**
     * Get the current page number, clamped to the valid range.
     *
     * @return int The current page number
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Set the current page number, clamped to the valid range.
     *
     * @param int $page The arbitrary page number
     */
    public function setCurrentPage(int $page): void
    {
        $this->currentPage = max(1, $page);
    }

    /**
     * Whether there is a next page?
     */
    public function hasNext(): bool
    {
        $total = $this->questionRepository->countSearchResults(
            query: $this->query,
            type: $this->type,
        );

        $totalPage = ceil($total / QuestionRepository::$pageSize);

        return $this->getCurrentPage() < $totalPage;
    }

    /**
     * Whether there is a previous page?
     */
    public function hasPrevious(): bool
    {
        return $this->getCurrentPage() > 1;
    }

    /**
     * Go to the next page.
     */
    #[LiveAction]
    public function nextPage(): void
    {
        $this->setCurrentPage($this->getCurrentPage() + 1);
    }

    /**
     * Go to the previous page.
     */
    #[LiveAction]
    public function previousPage(): void
    {
        $this->setCurrentPage($this->getCurrentPage() - 1);
    }

    /**
     * Filter the returning questions by type.
     *
     * @param string|null $type The type to filter
     */
    #[LiveAction]
    public function setTypeFilter(#[LiveArg] ?string $type): void
    {
        $this->type = $type ?? '';
    }
}
