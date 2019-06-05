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

class CoveragePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->compileCoverageOptions($container);
        $this->compileFilterOptions($container);
    }

    private function compileCoverageOptions(ContainerBuilder $container)
    {
        $options = $container->getParameterBag()->get('doyo.coverage.options');

        $definitions = $container->findTaggedServiceIds('doyo.code_coverage');

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
