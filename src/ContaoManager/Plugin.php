<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\SurveyBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use HeimrichHannot\ContaoExporterBundle\HeimrichHannotContaoExporterBundle;
use HeimrichHannot\SurveyBundle\HeimrichHannotContaoSurveyBundle;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;

class Plugin implements BundlePluginInterface, ExtensionPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotContaoSurveyBundle::class)->setLoadAfter([ContaoCoreBundle::class, HeimrichHannotContaoExporterBundle::class]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        if (in_array('HeimrichHannot\EncoreBundle\HeimrichHannotContaoEncoreBundle', $container->getParameter('kernel.bundles'), true)) {
            $extensionConfigs = ContainerUtil::mergeConfigFile('huh_encore', $extensionName, $extensionConfigs, __DIR__ . '/../Resources/config/config_encore.yml');
        }

        return $extensionConfigs;
    }
}

