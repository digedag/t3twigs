<?php

namespace System25\T3twigs\Typo3;

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

use Sys25\RnBase\Configuration\Processor;
use System25\T3twigs\Twig\RendererTwig;
use System25\T3twigs\Twig\T3TwigsException;
use tx_rnbase;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class TwigContentObject extends AbstractContentObject
{
    private $renderer;

    public function __construct($cObject = null)
    {
        if (!($cObject instanceof RendererTwig)) {
            parent::__construct($cObject);
        } else {
        }

        $this->renderer = $cObject instanceof RendererTwig ? $cObject : tx_rnbase::makeInstance(RendererTwig::class);
    }

    /**
     * @param string                $name
     * @param array                 $configuration
     * @param string                $typoscriptKey
     * @param ContentObjectRenderer $contentObject
     *
     * @return string
     */
    public function cObjGetSingleExt($name, array $configuration, $typoscriptKey, $contentObject)
    {
        $this->cObj = $contentObject;

        return $this->render($configuration);
    }

    /**
     * Rendering the cObject, TEMPLATE.
     *
     * @param array $conf Array of TypoScript properties
     *
     * @return string Output
     *
     * @see substituteMarkerArrayCached()
     *
     * @throws T3TwigException
     */
    public function render(
        $conf = []
    ) {
        $configurations = $this->buildConfigurations($conf);

        $contextData = $this->getContext($configurations);

        $content = $this->renderer->render($contextData, $configurations);

        return $content;
    }

    /**
     * Builds the configuration object based on the conf.
     *
     * @param array $conf
     *
     * @return Processor
     */
    private function buildConfigurations(
        array $conf
    ) {
        /** @var $configurations Processor */
        $configurations = tx_rnbase::makeInstance(
            Processor::class
        );
        $configurations->init(
            $conf,
            $this->getContentObjectRenderer(),
            't3twigs',
            't3twigs'
        );

        return $configurations;
    }

    /**
     * Compile rendered content objects in variables array ready to assign to the view.
     *
     * @param Processor $configurations
     *
     * @return array the variables to be assigned
     *
     * @throws T3TwigException
     */
    protected function getContext($configurations)
    {
        $contextData = [];
        $contextNames = $configurations->getKeyNames('context.');
        if (empty($contextNames)) {
            $contextNames = $configurations->getKeyNames('variables.');
        }
        $reservedVariables = ['data', 'current'];
        foreach ($contextNames as $key) {
            if (!in_array($key, $reservedVariables)) {
                $contextData[$key] = $configurations->get('context.'.$key, true);
            } else {
                throw new T3TwigsException('Cannot use reserved name "'.$key.'" as variable name in TWIGTEMPLATE.', 1288095720);
            }
        }

        $contextData['data'] = $this->getContentObjectRenderer()->data;
        $contextData['current'] = $this->getContentObjectRenderer()->data[$this->getContentObjectRenderer()->currentValKey] ?? '';

        return $contextData;
    }
}
