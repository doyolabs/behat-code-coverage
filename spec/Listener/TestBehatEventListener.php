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

namespace spec\Doyo\Behat\Coverage\Listener;

use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Listener\BehatEventListener;

class TestBehatEventListener extends BehatEventListener
{
    /**
     * @return CoverageEvent
     */
    public function getCoverageEvent(): CoverageEvent
    {
        return $this->coverageEvent;
    }

    /**
     * @param CoverageEvent $coverageEvent
     */
    public function setCoverageEvent(CoverageEvent $coverageEvent)
    {
        $this->coverageEvent = $coverageEvent;
    }
}
