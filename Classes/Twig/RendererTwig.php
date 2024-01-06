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

use Exception;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Utility\Files;
use System25\T3twigs\Twig\Extension\AbstractExtension;
use System25\T3twigs\Twig\Loader\T3FileSystem;
use System25\T3twigs\Typo3\TYPO3Cache;
use Throwable;
use Twig\Extension\ExtensionInterface;
use Twig\Template;
use Twig_Error_Runtime;
use tx_rnbase;

class RendererTwig
{
    /** @var TYPO3Cache */
    private $cache;

    /**
     * An instance of this renderer.
     *
     * @return RendererTwig
     */
    public static function instance(
    ) {
        return tx_rnbase::makeInstance(self::class);
    }

    public function __construct(
        TYPO3Cache $cache = null
    ) {
        $this->cache = $cache ?: tx_rnbase::makeInstance(TYPO3Cache::class);
    }

    /**
     * Renders the viewdata throu a template.
     *
     * @param array $data
     *
     * @return string The filan template
     *
     * @throws T3TwigException
     * @throws Exception
     * @throws \TYPO3\CMS\Core\Exception
     * @throws Throwable
     * @throws Twig_Error_Runtime
     */
    public function render(
        array $data,
        ConfigurationInterface $configurations,
        $confId = '',
        $templateFile = ''
    ) {
        $context = new RenderingContext(
            $configurations,
            $confId,
            $templateFile
        );
        $templateFullFilePath = $context->getTemplatePath();

        if (!is_file($templateFullFilePath)) {
            throw new T3TwigsException('Template file not found or empty: '.$templateFullFilePath);
        }

        $twigLoader = new T3FileSystem(dirname($templateFullFilePath));

        foreach ($context->getTemplatePaths() as $namespace => $path) {
            $twigLoader->addPath(
                Files::getFileAbsFileName($path),
                $namespace
            );
        }

        $twigEnv = $this->getTwigEnvironment($twigLoader)
            ->setRenderingContext($context);

        $this->injectExtensions(
            $twigEnv,
            $context->getExtensions()
        );

        /**
         * @var Template
         */
        $template = $twigEnv->loadTemplate(
            basename($templateFullFilePath)
        );

        $result = $template->render($data);

        return $result;
    }

    /**
     * Inject Twig Extensions by TS Config.
     *
     * @param EnvironmentTwig $environment
     * @param array $extensions
     *
     * @throws T3TwigsException
     */
    private function injectExtensions(
        EnvironmentTwig $environment,
        array $extensions
    ) {
        foreach ($extensions as $extension => $value) {
            /**
             * @var AbstractExtension
             */
            $extInstance = tx_rnbase::makeInstance($value);

            /*
             * Is it a valid twig extension?
             */
            if (!$extInstance instanceof ExtensionInterface) {
                throw new T3TwigsException(sprintf('Twig extension must be an instance of ExtensionInterface; "%s" given.', is_object($extInstance) ? get_class($extInstance) : gettype($extInstance)));
            }

            /*
             * Is extension already enabled?
             */
            $extIdent = method_exists($extInstance, 'getName') ? $extInstance->getName() : get_class($extInstance);
            if ($environment->hasExtension($extIdent)) {
                continue;
            }

            $environment->addExtension($extInstance);
        }
    }

    /**
     * Returns an instance of twig environment.
     *
     * @param T3FileSystem $twigLoaderFilesystem twig loader filesystem
     * @param bool $debug enable debug
     *
     * @return EnvironmentTwig
     */
    public function getTwigEnvironment(
        T3FileSystem $twigLoaderFilesystem,
        $debug = true
    ) {
        /**
         * Some ToDos.
         *
         * @TODO: take care of debug configuration
         */
        $twigEnv = new EnvironmentTwig(
            $twigLoaderFilesystem,
            [
                'debug' => $debug,
                'cache' => $this->cache,
            ]
        );

        return $twigEnv;
    }
}
