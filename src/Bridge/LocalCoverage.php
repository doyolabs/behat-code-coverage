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

namespace Doyo\Behat\Coverage\Bridge;

use Behat\Testwork\Tester\Result\TestResult;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\CodeCoverage as ReportCodeCoverage;
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
            CoverageEvent::START        => 'onCoverageStarted',
            CoverageEvent::STOP         => 'onCoverageStopped',
            CoverageEvent::REFRESH      => 'onCoverageRefresh',
            ReportEvent::BEFORE_PROCESS => 'onBeforeReportProcess',
        ];
    }

    public function onCoverageStarted(CoverageEvent $event)
    {
        $this->coverage->start($event->getTestCase());
    }

    public function onCoverageStopped(CoverageEvent $event)
    {
        $coverage = $this->coverage;

        /*
        $results = $event->getTestResults();

        $coverage->stop();

        if($results->isPassed()){
            $this->coverage->setCurrentStatus(0);
        }
        $map = [
            TestResult::PASSED => 0,
            TestResult::FAILED => 0,
        ];
        */
        $coverage->stop();
        $data = $event->getCoverage();
        $coverage->append($data, $event->getTestCase());
    }

    public function onCoverageRefresh()
    {
        $this->coverage->clear();
    }

    public function onBeforeReportProcess(ReportEvent $event)
    {
        $coverage       = $this->coverage;
        $driver = $coverage->getDriver();
        $reportCoverage = new ReportCodeCoverage($driver);

        $reportCoverage->setData($coverage->getData(true));
        $reportCoverage->setTests($coverage->getTests());
        $event->setCoverage($reportCoverage);
    }
}
