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

namespace Doyo\Behat\Coverage\Listener;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CodeCoverageListener implements EventSubscriberInterface
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var TestCase[]
     */
    private $testCases;

    public function __construct(
        Processor $processor
    ) {
        $this->processor = $processor;
        $this->testCases = [];
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
        $this->processor->start($event->getTestCase());
    }

    public function onCoverageStopped(CoverageEvent $event)
    {
        $coverage = $this->processor;
        $data     = $event->getCoverage();
        $testCase = $event->getTestCase();

        $coverage->stop();
        $coverage->append($data, $event->getTestCase()->getName());
        $coverage->addTestCase($testCase);
    }

    public function onCoverageRefresh()
    {
        $this->processor->clear();
    }

    public function onBeforeReportProcess(ReportEvent $event)
    {
        $processor = $this->processor;

        $processor->complete();
        $event->setProcessor($processor);
    }
}
