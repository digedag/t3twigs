<?php

namespace System25\T3twigs\Twig\Extension;

/***************************************************************
 * Copyright notice
 *
 * (c) 2024 Rene Nitzsche (rene@system25.de)
 * (c) 2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use Sys25\RnBase\Domain\Model\DataModel;
use Sys25\RnBase\Frontend\Request\Parameters;
use System25\T3twigs\Twig\EnvironmentTwig;
use Twig\TwigFunction;

class RequestExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                't3gp',
                [$this, 'renderGetPost'],
                ['needs_environment' => true]
            ),
        ];
    }

    /**
     * @param EnvironmentTwig $env
     * @param string                           $paramName
     * @param array                            $arguments
     *
     * @return mixed|null
     */
    public function renderGetPost(
        EnvironmentTwig $env,
        $paramName,
        array $arguments = []
    ) {
        return $this->performCommand(
            function (DataModel $arguments) use ($env, $paramName) {
                $paths = explode('|', $paramName);
                $segment = array_shift($paths);

                if ($arguments->getGlobal()) {
                    $param = Parameters::_GP($segment);
                } else {
                    $param = $env->getParameters()->get($segment);
                }

                while (null !== ($segment = array_shift($paths))) {
                    if (!isset($param[$segment])) {
                        return null;
                    }
                    $param = $param[$segment];
                }

                // reduce empty parameters
                if (is_array($param) && $arguments->getNoEmpty()) {
                    foreach (array_keys($param, '') as $key) {
                        unset($param[$key]);
                    }
                }

                return $param;
            },
            $env,
            $arguments
        );
    }

    /**
     * Get Extension name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 't3twig_requestExtension';
    }
}
