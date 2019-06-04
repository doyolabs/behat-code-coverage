<?php

/*
 * This file is part of the DoyoUserBundle project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\Coverage;

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Doyo\Behat\Coverage\Compiler\DriverPass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class Extension implements ExtensionInterface
{
    public function process(ContainerBuilder $container)
    {
    }

    public function getConfigKey()
    {
        return 'doyo';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $config = new Configuration();
        $config->configure($builder);

        return $builder;
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadServices($container, $config);

        $container->setParameter('doyo.coverage.options', $config['coverage']);
    }

    private function loadServices(ContainerBuilder $container, array $config)
    {
        $locator = new FileLocator(__DIR__.'/Resources/config');
        $loader  = new XmlFileLoader($container, $locator);

        $loader->load('core.xml');
        $loader->load('drivers.xml');
        $loader->load('coverage.xml');

        $container->addCompilerPass(new DriverPass());
    }
}
