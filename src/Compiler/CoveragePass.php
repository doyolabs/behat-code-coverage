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

namespace Doyo\Behat\Coverage\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;

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

        $whitelist = $filter['whitelist'];
        $blackList = $filter['blacklist'];

        $files = [];
        foreach ($whitelist as $path) {
            $found = $this->findFiles($basePath, $path, $blackList);
            $files = array_merge($found, $files);
        }
        $container->setParameter('doyo.coverage.config.filter', $files);
    }

    private function findFiles($basePath, $path, $blacklist)
    {
        $lastPos = stripos($path, '/');
        $dir     = $path;
        $name    = null;
        if (false !== $lastPos) {
            $dir  = substr($path, 0, $lastPos);
            $name = substr($path, $lastPos + 1);
        }

        $finder = Finder::create()->in($basePath);
        $finder->path($dir);
        if (!is_null($name)) {
            $finder->name($name);
        }

        foreach ($blacklist as $l) {
            $finder->notPath($l);
        }

        $files = [];
        foreach ($finder->files() as $file) {
            $files[] = $file->getRealPath();
        }

        return $files;
    }
}
