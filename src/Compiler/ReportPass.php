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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ReportPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('doyo.coverage.report');

        // configure processors
        foreach ($container->findTaggedServiceIds('doyo.coverage.report.processor') as $id => $tagArguments) {
            $this->configureProcessor($container, $id, $tagArguments);
        }
        // configure report
        foreach ($services as $id => $tagArguments) {
            $this->configureReport($container, $id, $tagArguments);
        }
    }

    private function configureProcessor(ContainerBuilder $container, $id, array $tagArguments)
    {
        $definition = $container->getDefinition($id);
        $format     = $tagArguments[0]['format'];
        $options    = $container->getParameterBag()->get('doyo.coverage.report.'.$format);
        $class      = $container->getParameterBag()->get($id.'.class');
        $hasOptions = ['html', 'text', 'crap4j'];

        unset($options['target']);
        $definition->setClass($class);
        if (!empty($options) && \in_array($format, $hasOptions, true)) {
            $this->configureProcessorOptions($definition, $class, $options);
        }
    }

    private function configureProcessorOptions(Definition $definition, $class, $options)
    {
        $r           = new \ReflectionClass($class);
        $constructor = $r->getConstructor();
        $parameters  = [];

        foreach ($constructor->getParameters() as $reflectionParameter) {
            $paramName = $reflectionParameter->getName();
            if (!$reflectionParameter->isDefaultValueAvailable()) {
                return;
            }
            $value    = $reflectionParameter->getDefaultValue();
            $position = $reflectionParameter->getPosition();
            if (isset($options[$paramName])) {
                $value = $options[$paramName];
            }
            $parameters[$position] = $value;
        }
        $definition->setArguments($parameters);
    }

    private function configureReport(ContainerBuilder $container, $id, array $tagArguments)
    {
        $definition = $container->getDefinition($id);
        $format     = $tagArguments[0]['format'];
        $type       = $tagArguments[0]['type'];
        $config     = $container->getParameterBag()->get('doyo.coverage.report.'.$format);
        $class      = $container->getParameterBag()->get('doyo.coverage.report.class');
        $basePath   = $container->getParameterBag()->get('paths.base');
        $dispatcher = $container->getDefinition('doyo.coverage.dispatcher');

        $definition->setClass($class);

        if (isset($config['target'])) {
            $target = $basePath.'/'.$config['target'];
            $definition->addMethodCall('setTarget', [$target]);
            $definition->addMethodCall('setProcessor', [new Reference($id.'.processor')]);
            $definition->addMethodCall('setName', [$format]);
            $dispatcher->addMethodCall('addSubscriber', [new Reference($id)]);
            $this->ensureDir($type, $target);
        }
    }

    private function ensureDir($type, $target)
    {
        $dir = $target;
        if ('file' === $type) {
            $dir = \dirname($target);
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
}
