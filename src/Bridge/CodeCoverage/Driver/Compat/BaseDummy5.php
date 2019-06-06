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

namespace Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Compat;

use Doyo\Behat\Coverage\Event\CoverageEvent;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A dumb driver to prevent error.
 * @codeCoverageIgnore
 */
class BaseDummy5 implements Driver, EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CoverageEvent::STOP => ['stop', -1],
        ];
    }

    public function start($determineUnusedAndDead = true)
    {
    }

    public function stop()
    {
        return [];
    }
}
