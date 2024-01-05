<?php

namespace System25\T3twigs\Twig\Extension;

/***************************************************************
 * Copyright notice
 *
 * (c) 2024 Rene Nitzsche (rene@system25.de)
 * (c) 2017 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
use Sys25\RnBase\Domain\Model\DataModel;
use Sys25\RnBase\Utility\Arrays;
use System25\T3twigs\Twig\EnvironmentTwig;
use Twig\TwigFilter;
use Twig\TwigFunction;
use tx_rnbase;

class LinkExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                't3link',
                [$this, 'renderLink'],
                ['needs_environment' => true, 'is_safe' => ['html']]
            ),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(
                't3url',
                [$this, 'renderUrl'],
                ['needs_environment' => true]
            ),
        ];
    }

    /**
     * @param EnvironmentTwig $env
     * @param                 label
     * @param array $arguments
     *
     * @return string
     */
    public function renderLink(EnvironmentTwig $env, $label, array $arguments = [])
    {
        $arguments['label'] = $label;

        return $this->performCommand(
            function (DataModel $arguments) use ($env) {
                return $this->makeRnbaseLink($env, $arguments)->makeTag();
            },
            $env,
            $arguments
        );
    }

    /**
     * @param EnvironmentTwig $env
     * @param array           $arguments
     *
     * @return string
     */
    public function renderUrl(EnvironmentTwig $env, array $arguments = [])
    {
        return $this->performCommand(
            function (DataModel $arguments) use ($env) {
                return $this->makeRnbaseLink($env, $arguments)->makeUrl(false);
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
        return 't3twig_linkExtension';
    }

    /**
     * @param EnvironmentTwig              $env
     * @param DataModel $arguments
     *
     * @return \Sys25\RnBase\Utility\Link
     */
    private function makeRnbaseLink(
        EnvironmentTwig $env,
        DataModel $arguments
    ) {
        $params = $arguments->getParams() ? $arguments->getParams()->toArray() : [];
        $tsPath = $arguments->getTsPath();

        $primeval = $env->getConfigurations();
        //  this was recreated, if there are a overrule config
        $configurations = $primeval;
        $confId = $env->getConfId().'ts.';

        // we have additional configurations, merge them together in a new config object
        if ($arguments->hasTsConfig()) {
            $primeval = $env->getConfigurations();
            /**
             * @var Processor
             */
            $configurations = tx_rnbase::makeInstance(
                Processor::class
            );
            $config = $arguments->getTsConfig();
            $primevalConf = $primeval->get($confId.$tsPath);
            if (is_array($primevalConf)) {
                $config = Arrays::mergeRecursiveWithOverrule(
                    $primevalConf,
                    $config
                );
            }
            $config = ['link.' => $config];
            $configurations->init(
                $config,
                $primeval->getCObj(),
                $primeval->getExtensionKey(),
                $primeval->getQualifier()
            );

            $confId = '';
            $tsPath = 'link.';
        }

        // reduce empty parameters
        if ($configurations->getBool($confId.$tsPath.'skipEmptyParams')) {
            foreach (array_keys($params, '') as $key) {
                unset($params[$key]);
            }
        }

        // create link from original if overrule config exist (keep vars).
        $rnBaseLink = $primeval->createLink();
        $rnBaseLink->label($arguments->getLabel(), true);
        $rnBaseLink->initByTS($configurations, $confId.$tsPath, $params);
        // set destination only if set, so 0 for current page can be used
        if (!$arguments->isPropertyEmpty('destination')) {
            $rnBaseLink->destination($arguments->getDestination());
        }

        if (($extTarget = $configurations->get($confId.$tsPath.'extTarget'))) {
            $rnBaseLink->externalTargetAttribute($extTarget);
        }

        return $rnBaseLink;
    }
}
