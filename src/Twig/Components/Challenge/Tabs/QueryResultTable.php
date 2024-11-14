<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Tabs;

use App\Entity\ChallengeDto\QueryResultDto;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class QueryResultTable
{
    use DefaultActionTrait;
    use Pagination;

    /**
     * @var QueryResultDto the result of this query
     */
    #[LiveProp(updateFromParent: true)]
    public QueryResultDto $result;

    /**
     * @return array<string> the header
     */
    public function getHeader(): array
    {
        return $this->result->getResult()[0];
    }

    /**
     * @return array<array<string>> the rows
     */
    public function getRows(): array
    {
        return \array_slice($this->result->getResult(), 1);
    }

    /**
     * Get the paginated rows and another row to determine if there are more pages.
     *
     * @return array<array<string>>
     */
    protected function getData(): array
    {
        return \array_slice($this->getRows(), ($this->page - 1) * self::limit, self::limit + 1);
    }

    /**
     * Get the paginated rows.
     *
     * @return array<array<string>>
     */
    public function getPaginatedRows(): array
    {
        return \array_slice($this->getData(), 0, self::limit);
    }
}
