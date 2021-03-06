<?php
/**
 * MtMail - e-mail module for Zend Framework 2
 *
 * @link      http://github.com/mtymek/MtMail
 * @copyright Copyright (c) 2013-2014 Mateusz Tymek
 * @license   BSD 2-Clause
 */

namespace MtMail\Factory;

use MtMail\Renderer\RendererInterface;
use MtMail\Service\Composer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ComposerServiceFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return Composer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $configuration = $serviceLocator->get('Configuration');
        /** @var RendererInterface $renderer */
        $renderer = $serviceLocator->get($configuration['mt_mail']['renderer']);
        $service = new Composer($renderer);

        $enabledPlugins = array();

        // trigger E_DEPRECATED if old configuration key is used
        // TODO: remove this in 1.0-stable version
        if (isset($configuration['mt_mail']['plugins']) && is_array($configuration['mt_mail']['plugins'])) {
            trigger_error(
                "'plugins' configuration key is now deprecated and should be replaced with 'composer_plugins'",
                E_USER_DEPRECATED
            );
            $enabledPlugins = $configuration['mt_mail']['plugins'];
        }

        if (isset($configuration['mt_mail']['composer_plugins'])
            && is_array($configuration['mt_mail']['composer_plugins'])
        ) {
            $enabledPlugins = array_merge($enabledPlugins, $configuration['mt_mail']['composer_plugins']);
        }

        $pluginManager = $serviceLocator->get('MtMail\Service\ComposerPluginManager');

        foreach ($enabledPlugins as $plugin) {
            $service->getEventManager()->attachAggregate($pluginManager->get($plugin));
        }

        return $service;
    }
}
