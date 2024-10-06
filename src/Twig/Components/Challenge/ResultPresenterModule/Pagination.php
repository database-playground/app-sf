<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

/**
 * A basic pagination trait for tables.
 *
 * The trait requires the class to have a `getData` method, that returns
 * the `limit+1` data for the current page. The `limit+1` is used to check if
 * there is a next page.
 *
 * It provides two property and two actions to navigate through the data.
 *
 * * `hasPrevious` property to check if there is a previous page.
 * * `hasNext` property to check if there is a next page.
 * * `goPrevious` action to go to the previous page.
 * * `goNext` action to go to the next page.
 */
trait Pagination
{
    private const int limit = 7;

    #[LiveProp]
    public int $page = 1;

    /**
     * Get the data for the current page.
     */
    abstract public function getData(): array;

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return \count($this->getData()) > self::limit;
    }

    #[LiveAction]
    public function goPrevious(): void
    {
        --$this->page;
    }

    #[LiveAction]
    public function goNext(): void
    {
        ++$this->page;
    }

    public function getCurrentOffset(): int
    {
        return ($this->page - 1) * self::limit;
    }
}
