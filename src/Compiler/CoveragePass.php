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

namespace Doyo\Behat\Coverage\Compiler;

use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CoveragePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->compileFilterOptions($container);
        $this->processSessions($container);
        $this->compileCoverageOptions($container);

        $definition = $container->getDefinition('doyo.coverage.dispatcher');
        $tagged     = $container->findTaggedServiceIds('doyo.dispatcher.subscriber');

        foreach ($tagged as $id=>$arguments) {
            $definition->addMethodCall('addSubscriber', [new Reference($id)]);
        }
    }

    private function processSessions(ContainerBuilder $container)
    {
        $sessions             = $container->getParameterBag()->get('doyo.coverage.sessions');
        $codeCoverageOptions  = $container->getParameterBag()->get('doyo.coverage.options');

        $driverMap = [
            'local'  => $container->getParameterBag()->get('doyo.coverage.session.local.class'),
            'remote' => $container->getParameterBag()->get('doyo.coverage.session.remote.class'),
        ];
        foreach ($sessions as $name => $config) {
            $driver    = $config['driver'];
            $class     = $driverMap[$driver];
            $id        = 'doyo.coverage.sessions.'.$name;
            $driverId  = $this->createSessionDriverDefinition($container, $name, $config);

            $definition = new Definition($class);
            $definition->addTag('doyo.dispatcher.subscriber');
            $definition->addArgument(new Reference($driverId));
            $definition->addArgument($codeCoverageOptions);
            $definition->addArgument(new Reference('doyo.coverage.filter'));
            $definition->setPublic(true);

            if('remote' === $driver){
                $this->configureRemoteSession($container, $definition, $config);

            }

            $container->setDefinition($id, $definition);
        }
    }

    private function configureRemoteSession(ContainerBuilder $container, Definition $definition, array $config)
    {
        $mink = 'mink';
        if($container->has($mink)){
            $definition->addMethodCall('setMink', [new Reference($mink)]);
        }

        if(!isset($config['remote_url'])){
            throw new ConfigurationLoadingException(sprintf(
                'driver parameters: %s should be set when using code coverage remote driver',
                'coverage_url'
            ));
        }

        $client = $container->get('doyo.coverage.http_client');
        $definition->addMethodCall('setHttpClient', [$client]);
        $definition->addMethodCall('setRemoteUrl',[$config['remote_url']]);
    }

    private function createSessionDriverDefinition(ContainerBuilder $container, $name, $config)
    {
        $driver = $config['driver'];
        $map = [
            'local'  => 'doyo.coverage.local_session.class',
            'remote' => 'doyo.coverage.remote_session.class',
        ];
        $class      = $container->getParameterBag()->get($map[$driver]);
        $id         = 'doyo.coverage.sessions.'.$name.'.driver';
        $definition = new Definition($class);
        $definition->setPublic(true);
        $definition->addArgument($name);

        $container->setDefinition($id, $definition);
        return $id;
    }

    private function compileCoverageOptions(ContainerBuilder $container)
    {
        $options = $container->getParameterBag()->get('doyo.coverage.options');

        $definitions = $container->findTaggedServiceIds('doyo.coverage.processor');
        /* @var \Symfony\Component\DependencyInjection\Definition $definition */
        foreach ($definitions as $id => $test) {
            $definition = $container->getDefinition($id);
            $this->addCoverageOption($definition, $options);
        }
    }

    private function addCoverageOption(Definition $definition, array $options)
    {
        foreach ($options as $name => $value) {
            $method = 'set'.ucfirst($name);
            $definition->addMethodCall($method, [$value]);
        }
    }

    private function compileFilterOptions(ContainerBuilder $container)
    {
        $config     = $container->getParameterBag()->get('doyo.coverage.config');
        $filter     = $config['filter'];
        $basePath   = $container->getParameterBag()->get('paths.base');
        $definition = $container->getDefinition('doyo.coverage.filter');

        foreach ($filter as $options) {
            $options['basePath'] = $basePath;
            $this->filterWhitelist($definition, $options, 'add');
            $exclude = $options['exclude'];
            foreach ($exclude as $item) {
                $item['basePath'] = $basePath;
                $this->filterWhitelist($definition, $item, 'remove');
            }
        }
    }

    private function filterWhitelist(Definition $definition, $options, $method)
    {
        $basePath  = $options['basePath'];
        $suffix    = $options['suffix'] ?: '.php';
        $prefix    = $options['prefix'] ?: '';
        $type      = $options['directory'] ? 'directory' : 'file';
        $directory = $basePath.\DIRECTORY_SEPARATOR.$options['directory'];
        $file      = $basePath.\DIRECTORY_SEPARATOR.$options['file'];

        if (preg_match('/\/\*(\..+)/', $directory, $matches)) {
            $suffix    = $matches[1];
            $directory = str_replace($matches[0], '', $directory);
        }

        $methodSuffix = 'add' === $method ? 'ToWhitelist' : 'FromWhitelist';
        if ('directory' === $type) {
            $definition->addMethodCall($method.'Directory'.$methodSuffix, [$directory, $suffix, $prefix]);
        }

        if ('file' === $type) {
            $definition->addMethodCall($method.'File'.$methodSuffix, [$file]);
        }
    }
}
