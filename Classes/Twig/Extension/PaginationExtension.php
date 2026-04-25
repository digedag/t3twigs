<?php

namespace System25\T3twigs\Twig\Extension;

/***************************************************************
 * Copyright notice
 *
 * (c) 2026 Rene Nitzsche (rene@system25.de)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Sys25\RnBase\Utility\PageBrowser;
use System25\T3twigs\Twig\EnvironmentTwig;
use System25\T3twigs\Utility\Pagination\PageBrowserPaginatorProxy;
use Twig\TwigFunction;

class PaginationExtension extends AbstractExtension
{
    /**
     * Twig Functions.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'getPagination',
                [$this, 'renderGetPagination'],
                ['needs_environment' => true, 'is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Creates output based on TypoScript.
     *
     * @param EnvironmentTwig $env
     * @param string $confId
     * @param array $arguments
     *
     * @return string
     * @throws Exception
     */
    public function renderGetPagination(
        EnvironmentTwig $env,
        PageBrowser $pageBrowser,
        $confId,
        array $arguments = []
    ) {
        // 1. Proxy erstellen
        $paginator = new PageBrowserPaginatorProxy($pageBrowser);
        $cfg = $env->getConfigurations();
        if (!str_starts_with($confId, 'lib.')) {
            $confId = sprintf('%sts.%s', $env->getConfId(), $confId);
        }

        // 2. Pagination-Logik von TYPO3 nutzen (Sliding Window)
        // Hier kannst du maxPages aus den arguments oder TypoScript holen
        $maxPages = $arguments['maxPages'] ?? $cfg->get($confId.'maxPages') ?? 10;
        $pagination = new \TYPO3\CMS\Core\Pagination\SlidingWindowPagination($paginator, $maxPages);

        $pointer = $pageBrowser->getPointer();
        $state = $pageBrowser->getState();
        $count = $pageBrowser->getListSize();
        $rangeFrom = $state['offset'] + 1;
        $totalPages = $pagination->getLastPageNumber();
        $rangeTo = ($pointer != $totalPages - 1) ? ($pointer + 1) * $state['limit'] : $count;

        // 3. Daten für Twig aufbereiten
        // Wir geben ein Array zurück, das alle nötigen Infos für das Template enthält
        return [
            'paginator' => $paginator,
            'pagination' => $pagination,
            'pages' => $pagination->getAllPageNumbers(), // Das Array der anzuzeigenden Seitenzahlen
            'current' => $paginator->getCurrentPageNumber() - 1,
            'displayStart' => $pagination->getDisplayRangeStart() - 1,
            'displayEnd' => $pagination->getDisplayRangeEnd() - 1,
            'first' => $pagination->getFirstPageNumber() - 1,
            'last' => $pagination->getLastPageNumber() - 1,
            'next' => $pagination->getNextPageNumber() ? $pagination->getNextPageNumber() - 1 : null,
            'prev' => $pagination->getPreviousPageNumber() ? $pagination->getPreviousPageNumber() - 1 : null,
            'param' => $pageBrowser->getParamName('pointer'),
            'confId' => $confId,
            'shouldRender' => count($pagination->getAllPageNumbers()) > 1 || !$cfg->getBool($confId.'hideIfSinglePage', false, true),
            'maxPages' => $maxPages,
            'status' => [
                'count' => $count,
                'rangeFrom' => $rangeFrom,
                'rangeTo' => $rangeTo,
                'totalPages' => $totalPages,
            ],
        ];
    }

    public function getName(): string
    {
        return 't3twigs_paginationExtension';
    }
}
