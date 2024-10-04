<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Table
{
    use DefaultActionTrait;
    use Pagination;

    /**
     * @var array<array<string, mixed>>
     */
    #[LiveProp(updateFromParent: true)]
    public array $result;

    /**
     * @return array<string>
     */
    public function getHeader(): array
    {
        if (0 === \count($this->result)) {
            return [];
        }

        return array_keys($this->result[0]);
    }

    /**
     * Get the data that can be paginated.
     *
     * It includes `[0, self::$LIMIT+1]` elements, where the last
     * element is used to determine if there are more pages.
     *
     * @return array<array<string, mixed>>
     */
    protected function getData(): array
    {
        return \array_slice($this->result, ($this->page - 1) * self::$limit, self::$limit + 1);
    }

    /**
     * Get the paginated data.
     *
     * @return array<array<string, mixed>>
     */
    public function getRows(): array
    {
        return \array_slice($this->getData(), 0, self::$limit);
    }
}
