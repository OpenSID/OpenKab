<?php

namespace App\Services;

use League\Fractal\Pagination\PaginatorInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class IlluminatePaginatorAdapter implements PaginatorInterface
{
    public function __construct(protected LengthAwarePaginator $paginator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage(): int
    {
        return $this->paginator->currentPage();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastPage(): int
    {
        return $this->paginator->lastPage();
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal(): int
    {
        return $this->paginator->total();
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(): int
    {
        return $this->paginator->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getPerPage(): int
    {
        return $this->paginator->perPage();
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(int $page): string
    {
        return urldecode($this->paginator->url($page));
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginator(): LengthAwarePaginator
    {
        return $this->paginator;
    }
}