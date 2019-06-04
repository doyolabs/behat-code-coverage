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
use Doyo\Behat\Coverage\Event\ReportEvent;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProxyCoverage implements EventSubscriberInterface
{
    /**
     * @var CodeCoverage
     */
    private $coverage;

    public static function getSubscribedEvents()
    {
        return [
            CoverageEvent::START        => 'onCoverageStarted',
            CoverageEvent::STOP         => ['onCoverageStopped', 10],
            CoverageEvent::REFRESH      => 'onCoverageRefresh',
            ReportEvent::BEFORE_PROCESS => 'onBeforeReport',
        ];
    }

    /**
     * ProxyCoverage constructor.
     *
     * @param CodeCoverage $coverage
     */
    public function __construct(
        CodeCoverage $coverage
    ) {
        $this->coverage = $coverage;
    }

    public function onCoverageStarted(CoverageEvent $event)
    {
    }

    public function onCoverageStopped(CoverageEvent $event)
    {
    }

    public function onCoverageRefresh(RefreshEvent $event)
    {
    }

    public function onBeforeReport(ReportEvent $event)
    {
    }
}
