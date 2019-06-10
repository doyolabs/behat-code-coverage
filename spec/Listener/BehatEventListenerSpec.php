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

use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Doyo\Behat\Coverage\Listener\BehatEventListener;
use Doyo\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Bridge\CodeCoverage\TestCase;
use Doyo\Symfony\Bridge\EventDispatcher\EventDispatcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BehatEventListenerSpec extends ObjectBehavior
{
    public function let(
        EventDispatcher $dispatcher,
        ProcessorInterface $processor,
        ConsoleIO $consoleIO
    ) {
        $this->beConstructedWith($dispatcher, $processor, $consoleIO);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(BehatEventListener::class);
    }

    public function it_should_listen_to_behat_events()
    {
        $this->getSubscribedEvents()->shouldHaveKey(ExerciseCompleted::BEFORE);
        $this->getSubscribedEvents()->shouldHaveKey(ExerciseCompleted::AFTER);
    }

    public function it_should_dispatch_coverage_refresh_event(
        EventDispatcher $dispatcher,
        ProcessorInterface $processor
    ) {
        $processor->clear()->shouldBeCalled();

        $dispatcher
            ->dispatch(Argument::type(CoverageEvent::class), CoverageEvent::BEFORE_REFRESH)
            ->shouldBeCalled();
        $dispatcher
            ->dispatch(Argument::type(CoverageEvent::class), CoverageEvent::REFRESH)
            ->shouldBeCalled();

        $this->refreshCoverage();
    }

    public function it_should_dispatch_coverage_start_event(
        EventDispatcher $dispatcher,
        ScenarioTested $scope,
        ScenarioInterface $scenario,
        FeatureNode $feature,
        TestResult $results,
        ProcessorInterface $processor
    ) {
        $scope->getFeature()->willReturn($feature);
        $scope->getScenario()->willReturn($scenario);
        $feature->getFile()->willReturn('some.feature');
        $scenario->getLine()->willReturn('line');
        $results->getResultCode()->willReturn(TestResults::PASSED);

        $processor->start(Argument::type(TestCase::class))->shouldBeCalled();
        $dispatcher
            ->dispatch(Argument::type(CoverageEvent::class), CoverageEvent::BEFORE_START)
            ->shouldBeCalled();
        $dispatcher
            ->dispatch(Argument::type(CoverageEvent::class), CoverageEvent::START)
            ->shouldBeCalled();

        $this->startCoverage($scope);
    }

    public function it_should_dispatch_coverage_stop_event(
        EventDispatcher $dispatcher,
        AfterTested $afterTested,
        TestResult $result,
        TestCase $testCase,
        ProcessorInterface $processor
    ) {
        $afterTested->getTestResult()->willReturn($result)->shouldBeCalledOnce();
        $result->getResultCode()->willReturn(0)->shouldBeCalledOnce();

        $testCase->setResult(0)->shouldBeCalled();
        $processor
            ->getCurrentTestCase()
            ->shouldBeCalledOnce()
            ->willReturn($testCase);
        $processor->stop()->shouldBeCalled();

        $dispatcher->dispatch(Argument::type(CoverageEvent::class), CoverageEvent::BEFORE_STOP)
            ->shouldBeCalled();
        $dispatcher->dispatch(Argument::type(CoverageEvent::class), CoverageEvent::STOP)
            ->shouldBeCalled();

        $this->stopCoverage($afterTested);
    }

    public function it_should_dispatch_report_events(
        EventDispatcher $dispatcher,
        ProcessorInterface $processor
    ) {
        $processor->complete()->shouldBeCalled();

        $dispatcher
            ->dispatch(Argument::any(), CoverageEvent::COMPLETED)
            ->shouldBeCalled();
        $dispatcher
            ->dispatch(Argument::any(), ReportEvent::BEFORE_PROCESS)
            ->shouldBeCalled();
        $dispatcher
            ->dispatch(Argument::any(), ReportEvent::PROCESS)
            ->shouldBeCalled();
        $dispatcher
            ->dispatch(Argument::any(), ReportEvent::AFTER_PROCESS)
            ->shouldBeCalled();

        $this->generateReport();
    }
}
