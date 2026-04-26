<?php

namespace System25\T3twigs\ViewHelpers;

use Sys25\RnBase\Configuration\Processor;
use Sys25\RnBase\Utility\TYPO3;
use System25\T3twigs\Twig\RendererTwig;
use tx_rnbase;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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

/**
 * "ViewHelper" to render template by Twig.
 *
 * # Example: Basic example
 * <code>
 * <t:renderTwig path="{settings.cssFile}" />
 * </code>
 * <output>
 * The rendered twig output.
 * </output>
 */
class RenderTwigViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /** @var RendererTwig */
    private $renderer;

    public function __construct($renderer = null)
    {
        $this->renderer = $renderer ?: tx_rnbase::makeInstance(RendererTwig::class);
    }

    public function initializeArguments()
    {
        $this->registerArgument('template', 'string', 'Path to twig template', true);
        $this->registerArgument('settings', 'array', 'TS settings', false, false);
        $this->registerArgument('context', 'array', 'context', false, []);
    }

    public function render()
    {
        $template = $this->arguments['template'];
        $settings = $this->arguments['settings'];
        $tsfe = TYPO3::getTSFE();

        $configurations = $this->buildConfigurations($settings, $tsfe->cObj);
        $content = $this->renderer->render(
            $this->arguments['context'],
            $configurations,
            '',
            $template
        );

        return $content;
    }

    /**
     * Builds the  configuration object based on the conf.
     *
     * @param array $conf
     *
     * @return \Sys25\RnBase\Configuration\ConfigurationInterface
     */
    private function buildConfigurations(array $conf, $cObj)
    {
        /* @var $configurations \Sys25\RnBase\Configuration\ConfigurationInterface */
        $configurations = tx_rnbase::makeInstance(
            Processor::class
        );
        $configurations->init(
            $conf,
            $cObj,
            't3twigs',
            't3twigs'
        );

        return $configurations;
    }
}
