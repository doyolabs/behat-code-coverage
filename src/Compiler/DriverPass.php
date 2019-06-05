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

use SebastianBergmann\CodeCoverage\Version;
use SebastianBergmann\Environment\Runtime;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DriverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->fixCompatClass($container);
        $this->setupLocalDriver($container);
    }

    private function fixCompatClass(ContainerBuilder $container)
    {
        $version = Version::id();
        if (version_compare($version, '6.0', '<')) {
            $container->getParameterBag()->set('doyo.coverage.driver.dummy.class', 'Doyo\\Behat\\Coverage\\Bridge\\Driver\\Compat\\Dummy');
        }
    }

    private function setupLocalDriver(ContainerBuilder $container)
    {
        $runtime = new Runtime();

        if (!$runtime->canCollectCodeCoverage()) {
            $driver = 'doyo.coverage.driver.dummy';
        } elseif ($runtime->isPHPDBG()) {
            $driver = 'doyo.coverage.driver.phpdbg';
        } else {
            $driver = 'doyo.coverage.driver.xdebug';
        }

        $container->setAlias('doyo.coverage.local.driver', $driver);
    }
}
