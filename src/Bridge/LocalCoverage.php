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

namespace Doyo\Behat\Coverage\Bridge;

use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\RefreshEvent;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocalCoverage implements EventSubscriberInterface
{
    private $coverage;

    public function __construct(
        CodeCoverage $coverage
    ) {
        $this->coverage = $coverage;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoverageEvent::START   => 'onCoverageStarted',
            CoverageEvent::STOP    => 'onCoverageStopped',
            CoverageEvent::REFRESH => 'onCoverageRefresh',
        ];
    }

    public function onCoverageStarted(CoverageEvent $event)
    {
        $this->coverage->start($event->getCoverageId());
    }

    public function onCoverageStopped(CoverageEvent $event)
    {
        $coverage = $this->coverage;

        $coverage->stop();
        $data = $coverage->getData(true);
        $event->updateCoverage($data);
    }

    public function onCoverageRefresh(RefreshEvent $event)
    {
    }
}
