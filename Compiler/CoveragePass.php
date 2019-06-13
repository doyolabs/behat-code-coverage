<?php

/*
 * This file is part of the doyo/code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\CodeCoverage\Compiler;

use Doyo\Behat\CodeCoverage\Listener\CoverageListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CoveragePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $listener = new Definition(CoverageListener::class);
        $listener->addArgument(new Reference('doyo.coverage'));
        $listener->addArgument($container->getParameterBag()->get('doyo.coverage_enabled'));
        $listener->addTag('event_dispatcher.subscriber');

        $container->setDefinition('doyo.coverage.listener', $listener);
    }
}
