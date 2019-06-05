<?php

/*
 * This file is part of the doyo/behat-coverage-extension project.
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
use Doyo\Behat\Coverage\Compiler\CoveragePass;
use Doyo\Behat\Coverage\Compiler\DriverPass;
use Doyo\Behat\Coverage\Compiler\ReportPass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class Extension implements ExtensionInterface
{
    public function process(ContainerBuilder $container)
    {
        /*
        $definition = $container->getDefinition('doyo.coverage.dispatcher');
        foreach ($container->findTaggedServiceIds('doyo.dispatcher.subscriber') as $id=>$arguments) {
            $service  = new Reference($id);
            $priority = $arguments[0];
            $priority = $priority['priority'] ?? null;
            $definition->addMethodCall('addSubscriber', [$service, $priority]);
        }
        */
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
        $this->loadServices($container);

        $container->setParameter('doyo.coverage.options', $config['coverage']);
        $container->setParameter('doyo.coverage.config', $config);
        $container->setParameter('doyo.coverage.drivers', $config['drivers']);

        $reportFormats = ['clover', 'crap4j', 'html', 'php', 'text', 'xml'];
        foreach ($reportFormats as $format) {
            $name = 'doyo.coverage.report.'.$format;
            $container->setParameter($name, $config['report'][$format]);
        }
    }

    private function loadServices(ContainerBuilder $container)
    {
        $locator = new FileLocator(__DIR__.'/Resources/config');
        $loader  = new XmlFileLoader($container, $locator);

        $loader->load('core.xml');
        $loader->load('drivers.xml');
        $loader->load('coverage.xml');
        $loader->load('report.xml');

        $container->addCompilerPass(new DriverPass());
        $container->addCompilerPass(new ReportPass());
        $container->addCompilerPass(new CoveragePass());
    }
}
