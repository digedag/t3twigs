<?php

namespace System25\T3twigs\Twig;

use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Utility\Arrays;
use Sys25\RnBase\Utility\Files;
use Sys25\RnBase\Utility\TYPO3;

/***************************************************************
 * Copyright notice
 *
 * (c) 2024 Rene Nitzsche (rene@system25.de)
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

class RenderingContext
{
    /**
     * Basic conf from lib.tx_t3twigs.
     *
     * @var array
     */
    protected $conf;

    /**
     * Filepath to alternativ fallback template.
     *
     * @var string
     */
    protected $fallbackTemplate;

    /**
     * Filepath to alternativ fallback template.
     *
     * @var \Sys25\RnBase\Configuration\ConfigurationInterface
     */
    protected $configurations;

    /**
     * Configuration path.
     *
     * @var string
     */
    protected $confId;

    /**
     * Constructor.
     *
     * @param ConfigurationInterface $configurations
     * @param string $confId
     * @param array $conf
     */
    public function __construct(
        ConfigurationInterface $configurations,
        string $confId = '',
        string $templateFile = ''
    ) {
        $this->conf = [];
        if (isset(TYPO3::getTSFE()->tmpl->setup['lib.']['tx_t3twigs.'])) {
            $this->conf = TYPO3::getTSFE()->tmpl->setup['lib.']['tx_t3twigs.'];
        }
        $this->configurations = $configurations;
        $this->confId = $confId;
        $this->fallbackTemplate = $templateFile;
    }

    public function getConf(): array
    {
        return $this->conf;
    }

    public function getConfId(): string
    {
        return $this->confId;
    }

    public function getConfigurations(): ConfigurationInterface
    {
        return $this->configurations;
    }

    private function getFallbackTemplate(): string
    {
        return $this->fallbackTemplate;
    }

    /**
     * The template path with the template for the current renderer.
     *
     * @return string
     *
     * @throws T3TwigException
     */
    public function getTemplatePath()
    {
        $path = $this->getConfigurations()->get($this->getConfId().'file', true);
        $path = $path ?: $this->getConfigurations()->get($this->getConfId().'template', true);

        if (empty($path)) {
            $path = $this->getFallbackTemplate();
        }

        // if the path only contains the filename like `Detail.html.twig`
        // so we try to add the base template path from the configuration.
        if (!empty($path) && false === strpos($path, '/')) {
            $basePath = $this->getConfigurations()->get('templatePath');
            // add the first template include path
            $basePath = $basePath ?: reset((array) $this->conf['templatepaths.']);
            if (!empty($basePath)) {
                $path = $basePath.'/'.$path;
            }
        }

        if (empty($path)) {
            throw new T3TwigsException('Neither "file" nor "template" configured for twig template.');
        }

        return Files::getFileAbsFileName($path);
    }

    /**
     * The template path to search in for macros, includes, tc.
     *
     * @return array
     */
    public function getTemplatePaths(): array
    {
        // initial use the global paths
        $paths = $this->conf['templatepaths.'] ?? [];
        // add the paths for the current render context
        $paths = Arrays::mergeRecursiveWithOverrule(
            $paths,
            $this->getConfigurations()->getExploded(
                $this->getConfId().'templatepaths.'
            )
        );

        return $paths;
    }

    /**
     * The extensions to use in twig templates.
     *
     * @return array
     */
    public function getExtensions(): array
    {
        // initial use the global paths
        $paths = $this->conf['extensions.'] ?: [];

        // add the paths for the current render context
        $paths = Arrays::mergeRecursiveWithOverrule(
            $paths,
            $this->getConfigurations()->getExploded(
                $this->getConfId().'extensions.'
            )
        );

        return $paths;
    }
}
