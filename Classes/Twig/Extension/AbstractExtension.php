<?php

namespace System25\T3twigs\Twig\Extension;

use Exception;
use Sys25\RnBase\Domain\Model\DataModel;
use Sys25\RnBase\Utility\TypoScript;
use System25\T3twigs\Twig\EnvironmentTwig;
use System25\T3twigs\Twig\T3twigsExtensionInterface;
use Twig\Extension\AbstractExtension as TwigAbstractExtension;

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

abstract class AbstractExtension extends TwigAbstractExtension implements T3twigsExtensionInterface
{
    /**
     * Initiate the arguments,
     * sets the content object data
     * and performs the command.
     *
     * @param callable        $callable
     * @param EnvironmentTwig $env
     * @param array           $arguments
     *
     * @return mixed
     * @throws Exception
     */
    protected function performCommand(
        $callable,
        EnvironmentTwig $env = null,
        $arguments = null
    ) {
        $cObj = $env->getContentObject();
        $exception = null;

        // backup content object data
        $cObjData = $cObj->data;

        // initialize
        $arguments = $this->initiateArguments($arguments, $env);

        try {
            // perform command
            $return = call_user_func($callable, $arguments);
        } catch (Exception $exception) {
            // the exception is thrown after the shutdown
        }

        // restore content object data
        $cObj->data = $cObjData;
        $cObj->setCurrentVal(false);

        // throw exception, if command thows one
        if ($exception instanceof Exception) {
            throw $exception;
        }

        return $return;
    }

    /**
     * Creates a new data instance.
     *
     * @param array|DataModel $arguments
     * @param EnvironmentTwig $env
     *
     * @return DataModel
     */
    protected function initiateArguments(
        $arguments = null,
        EnvironmentTwig $env = null
    ) {
        $arguments = DataModel::getInstance($arguments);

        if ($env instanceof EnvironmentTwig) {
            $this->setContentObjectData(
                $env,
                $arguments->getData(),
                $arguments->getCurrentValue()
            );
        }

        // convert ts config from  array to ts array
        if ($arguments->hasTsConfig()) {
            $arguments->setTsConfig(
                TypoScript::convertPlainArrayToTypoScriptArray(
                    $arguments->getTsConfig()->toArray()
                )
            );
        }

        return $arguments;
    }

    /**
     * Sets the data in the current content object and backups the current value.
     *
     * @param EnvironmentTwig $env
     * @param array           $data
     * @param string          $currentValue
     */
    protected function setContentObjectData(
        EnvironmentTwig $env,
        $data = null,
        $currentValue = null
    ) {
        $contentObject = $env->getContentObject();

        if (null === $data) {
            // nothing todo, if there are no data to set
        } elseif (is_scalar($data)) {
            $currentValue = $currentValue ?: (string) $data;
            $data = [$data];
        } elseif ($data instanceof DataModel) {
            $data = $data->toArray();
        }

        // set data
        if (null !== $data) {
            $contentObject->data = $data;
        }

        if (null !== $currentValue) {
            $contentObject->setCurrentVal($currentValue);
        }
    }
}
