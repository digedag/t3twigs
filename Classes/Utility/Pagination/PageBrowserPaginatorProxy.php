<?php

namespace System25\T3twigs\Utility\Pagination;

use Sys25\RnBase\Utility\PageBrowser;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;

class PageBrowserPaginatorProxy implements PaginatorInterface
{
    protected $pageBrowser;
    protected $itemsPerPage;
    protected $currentPageNumber;

    public function __construct(PageBrowser $pageBrowser)
    {
        $this->pageBrowser = $pageBrowser;
        $this->itemsPerPage = (int) $pageBrowser->getPageSize();
        // Umwandlung von 0-basiertem Pointer zu 1-basierter Seitenzahl
        $this->currentPageNumber = (int) $pageBrowser->getPointer() + 1;
    }

    public function withItemsPerPage(int $itemsPerPage): PaginatorInterface
    {
        $new = clone $this;
        $new->itemsPerPage = $itemsPerPage;

        return $new;
    }

    public function withCurrentPageNumber(int $currentPageNumber): PaginatorInterface
    {
        $new = clone $this;
        $new->currentPageNumber = $currentPageNumber;

        return $new;
    }

    public function getPaginatedItems(): iterable
    {
        return [];
    }

    public function getNumberOfPages(): int
    {
        $count = $this->pageBrowser->getListSize();

        return (int) ceil($count / $this->itemsPerPage);
    }

    public function getCurrentPageNumber(): int
    {
        return $this->currentPageNumber;
    }

    public function getKeyOfFirstPaginatedItem(): int
    {
        return ($this->currentPageNumber - 1) * $this->itemsPerPage;
    }

    public function getKeyOfLastPaginatedItem(): int
    {
        $lastItem = $this->currentPageNumber * $this->itemsPerPage - 1;
        $total = $this->pageBrowser->getListSize();

        return min($lastItem, $total - 1);
    }
}
