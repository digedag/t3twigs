<?php

namespace System25\T3twigs\Twig\Extension;

/*
 * *************************************************************
 * Copyright notice
 *
 * (c) 2024 Rene Nitzsche (rene@system25.de)
 * (c) 2019 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */

use Exception;
use Sys25\RnBase\Domain\Model\BaseModel;
use Sys25\RnBase\Search\SearchBase;
use Sys25\RnBase\Utility\Arrays;
use System25\T3twigs\Twig\EnvironmentTwig;
use System25\T3twigs\Twig\T3twigsExtensionInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use tx_rnbase;

class DBRelationExtension extends AbstractExtension implements T3twigsExtensionInterface
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('t3dbrel', [
                $this,
                'lookupRelation',
            ], [
                'needs_environment' => true,
            ]),
        ];
    }

    /**
     * @param EnvironmentTwig $env
     * @param string $paramName
     * @param array $arguments
     *
     * @return mixed|null
     */
    public function lookupRelation(
        EnvironmentTwig $env,
        BaseModel $entity,
        array $arguments = []
    ) {
        $confId = sprintf('%srelations.%s.', $env->getConfId(), htmlspecialchars($arguments['relation']));

        $alias = $env->getConfigurations()->get($confId.'join.alias');
        $field = $env->getConfigurations()->get($confId.'join.field');
        if (!$alias || !$field) {
            throw new Exception(sprintf("Verify config for relation '%s' Table alias or field not found. Full typoscript path: %s", htmlspecialchars($arguments['relation']), $confId));
        }

        $fields = $options = [];
        $fields[$alias.'.'.$field][OP_EQ_INT] = $entity->getUid();

        SearchBase::setConfigFields($fields, $env->getConfigurations(), $confId.'fields.');
        SearchBase::setConfigOptions($options, $env->getConfigurations(), $confId.'options.');

        if ($otherOptions = isset($arguments['options']) ? $arguments['options'] : []) {
            $options = Arrays::mergeRecursiveWithOverrule($options, $otherOptions);
        }

        if ($otherFields = isset($arguments['fields']) ? $arguments['fields'] : []) {
            $fields = Arrays::mergeRecursiveWithOverrule($fields, $otherFields);
        }

        $searcher = tx_rnbase::makeInstance($env->getConfigurations()->get($confId.'callback.class'));
        $method = $env->getConfigurations()->get($confId.'callback.method');

        return $searcher->$method($fields, $options);
    }

    /**
     * Get Extension name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 't3twig_dbrelationExtension';
    }
}
