<?php

/*
 * This file is part of the doyo/code-coverage project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\CodeCoverage;

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Doyo\Behat\CodeCoverage\Controller\CliController;
use Doyo\Behat\CodeCoverage\Listener\CoverageListener;
use Doyo\Bridge\CodeCoverage\Configuration;
use Doyo\Bridge\CodeCoverage\ContainerFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class Extension implements ExtensionInterface
{
    public function process(ContainerBuilder $container)
    {
    }

    public function getConfigKey()
    {
        return 'doyo_coverage';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
        // TODO: Implement initialize() method.
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $configuration = new Configuration();
        $configuration->configure($builder);

        return $builder;
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition(CliController::class);
        $definition->setPublic(true);
        $definition->addTag('cli.controller', ['priority' => 80000]);
        $container->setDefinition('doyo.coverage.cli_controller', $definition);

        // load listener
        $coverageContainer = (new ContainerFactory($config, true))->getContainer();
        $coverageContainer->set('console.input', $container->get('cli.input'));
        $coverageContainer->set('console.output', $container->get('cli.output'));

        $container->set('doyo.coverage.container', $coverageContainer);
        $container->set('doyo.coverage', $coverageContainer->get('coverage'));

        /* @var \Symfony\Component\Console\Input\InputInterface $input */
        $input           = $container->get('cli.input');
        $coverageEnabled = $input->hasParameterOption(['--coverage']);
        $container->setParameter('doyo.coverage_enabled', $coverageEnabled);

        $listener = new Definition(CoverageListener::class);
        $listener->addArgument(new Reference('doyo.coverage'));
        $listener->addArgument($container->getParameterBag()->get('doyo.coverage_enabled'));
        $listener->addTag('event_dispatcher.subscriber');

        $container->setDefinition('doyo.coverage.listener', $listener);
    }
}
