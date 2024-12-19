<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Tabs;

use App\Entity\SqlRunnerDto\SqlRunnerResult;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class SqlRunnerResultTable
{
    use DefaultActionTrait;
    use Pagination;

    /**
     * @var SqlRunnerResult the result of this query
     */
    #[LiveProp(updateFromParent: true)]
    public SqlRunnerResult $result;

    /**
     * Get the paginated rows.
     *
     * @return array<array<string>>
     */
    public function getPaginatedRows(): array
    {
        return \array_slice($this->getData(), 0, self::limit);
    }

    /**
     * Get the paginated rows and another row to determine if there are more pages.
     *
     * @return array<array<string>>
     */
    protected function getData(): array
    {
        return \array_slice($this->result->getRows(), ($this->page - 1) * self::limit, self::limit + 1);
    }
}
