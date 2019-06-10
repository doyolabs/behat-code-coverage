<?php

/*
 * This file is part of the doyo/behat-code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\Coverage\Compiler;

use SebastianBergmann\Environment\Runtime;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DriverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->setupLocalDriver($container);
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
