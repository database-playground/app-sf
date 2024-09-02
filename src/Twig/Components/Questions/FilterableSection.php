<?php

declare(strict_types=1);

namespace App\Twig\Components\Questions;

use App\Repository\QuestionRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class FilterableSection
{
    use DefaultActionTrait;

    public string $title = "題庫一覽";

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public int $currentPage = 1;

    public function __construct(
        public QuestionRepository $questionRepository,
    ) {}

    public function getQuestions(): array
    {
        return $this->questionRepository->search(
            query: $this->query,
            page: $this->currentPage,
        );
    }
}
