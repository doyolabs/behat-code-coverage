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

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Tester\Result\TestResult;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Bridge\Symfony\EventDispatcher;
use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BehatEventListener implements EventSubscriberInterface
{
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var CoverageEvent
     */
    protected $coverageEvent;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var ConsoleIO
     */
    private $consoleIO;

    public function __construct(
        EventDispatcher $dispatcher,
        ProcessorInterface $processor,
        ConsoleIO $consoleIO
    ) {
        $this->dispatcher = $dispatcher;
        $this->processor = $processor;
        $this->consoleIO = $consoleIO;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExerciseCompleted::BEFORE => 'refreshCoverage',
            ScenarioTested::BEFORE    => 'startCoverage',
            ExampleTested::BEFORE     => 'startCoverage',
            ScenarioTested::AFTER     => 'stopCoverage',
            ExampleTested::AFTER      => 'stopCoverage',
            ExerciseCompleted::AFTER  => 'generateReport',
        ];
    }

    public function refreshCoverage()
    {
        $dispatcher      = $this->dispatcher;
        $event           = new CoverageEvent($this->processor, $this->consoleIO);

        $this->processor->clear();
        $dispatcher->dispatch($event, CoverageEvent::BEFORE_REFRESH);
        $dispatcher->dispatch($event, CoverageEvent::REFRESH);
    }

    public function startCoverage($scope)
    {
        $scenario = $scope->getScenario();
        $id = $scope->getFeature()->getFile().':'.$scenario->getLine();
        $dispatcher = $this->dispatcher;
        $testCase = new TestCase($id);
        $processor = $this->processor;
        $consoleIO = $this->consoleIO;

        $coverageEvent = new CoverageEvent($processor, $consoleIO, $testCase);
        $processor->start($testCase);
        $dispatcher->dispatch($coverageEvent, CoverageEvent::BEFORE_START);
        $dispatcher->dispatch($coverageEvent, CoverageEvent::START);

        $this->coverageEvent = $coverageEvent;
    }

    public function stopCoverage(AfterTested $testedEvent)
    {
        $dispatcher    = $this->dispatcher;
        $coverageEvent = $this->coverageEvent;
        $testCase      = $coverageEvent->getTestCase();
        $result        = $testedEvent->getTestResult();
        $map           = [
            TestResult::PASSED  => TestCase::RESULT_PASSED,
            TestResult::FAILED  => TestCase::RESULT_FAILED,
            TestResult::SKIPPED => TestCase::RESULT_SKIPPED,
            TestResult::PENDING => TestCase::RESULT_SKIPPED,
        ];
        $processor = $this->processor;
        $result    = $map[$result->getResultCode()];

        $testCase->setResult($result);
        $processor->stop();
        $processor->addTestCase($testCase);

        $dispatcher->dispatch($coverageEvent, CoverageEvent::BEFORE_STOP);
        $dispatcher->dispatch($coverageEvent, CoverageEvent::STOP);
    }

    public function generateReport()
    {
        $dispatcher    = $this->dispatcher;
        $processor     = $this->processor;
        $consoleIO = $this->consoleIO;


        $consoleIO->setHasError(false);

        $coverageEvent = new CoverageEvent($processor, $consoleIO);
        $dispatcher->dispatch($coverageEvent, CoverageEvent::COMPLETED);
        $processor->complete();

        $reportEvent   = new ReportEvent($processor, $consoleIO);
        $dispatcher->dispatch($reportEvent, ReportEvent::BEFORE_PROCESS);
        $dispatcher->dispatch($reportEvent, ReportEvent::PROCESS);
        $dispatcher->dispatch($reportEvent, ReportEvent::AFTER_PROCESS);
    }
}
