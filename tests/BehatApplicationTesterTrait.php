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

/*
 * This file is part of the doyo/behat-code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Doyo\Behat\Coverage;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Test\Doyo\Behat\Coverage\Fixtures\Application;
use Test\Doyo\Behat\Coverage\Fixtures\ApplicationFactory;

trait BehatApplicationTesterTrait
{
    /**
     * @var ApplicationFactory
     */
    private static $applicationFactory;

    /**
     * @var Application
     */
    private static $application;

    final protected function initConfigPath($configPath)
    {
        static::$applicationFactory->setConfigPath($configPath);
        static::$application = null;
    }

    /**
     * @return ApplicationFactory
     */
    final protected function getApplicationFactory()
    {
        if (null === static::$applicationFactory) {
            static::$applicationFactory = new ApplicationFactory();
        }

        return static::$applicationFactory;
    }

    /**
     * @return Application|ApplicationFactory
     */
    final protected function getApplication()
    {
        if (null === static::$application) {
            static::$application = $this->getApplicationFactory()->createApplication();
        }

        return static::$application;
    }

    /**
     * @return ContainerInterface
     */
    final protected function getContainer()
    {
        return $this->getApplication()->getContainer();
    }
}
