<?php

namespace System25\T3twigs\Twig;

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

use System25\T3twigs\Twig\RendererTwig as Renderer;
use Twig\Environment;

class EnvironmentTwig extends Environment
{
    /**
     * @var RenderingContext
     */
    protected $renderingContext;

    /**
     * Sets the current renderer.
     *
     * @param Renderer $renderer
     *
     * @return $this
     */
    public function setRenderingContext(
        RenderingContext $context
    ) {
        $this->renderingContext = $context;

        return $this;
    }

    /**
     * The current configurations.
     *
     * @return \Sys25\RnBase\Configuration\ConfigurationInterface
     */
    public function getConfigurations()
    {
        return $this->renderingContext->getConfigurations();
    }

    /**
     * The current configurations object.
     *
     * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public function getContentObject()
    {
        return $this->getConfigurations()->getContentObject();
    }

    /**
     * The current confId.
     *
     * @return string
     */
    public function getConfId()
    {
        return $this->renderingContext->getConfId();
    }

    /**
     * @return \Sys25\RnBase\Frontend\Request\Parameters
     */
    public function getParameters()
    {
        return $this->getConfigurations()->getParameters();
    }
}
