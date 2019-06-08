<?php

namespace spec\Doyo\Behat\Coverage\Listener;

use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\RefreshEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Doyo\Behat\Coverage\Listener\BehatEventListener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doyo\Behat\Coverage\Bridge\Symfony\EventDispatcher;

class BehatEventListenerSpec extends ObjectBehavior
{
    function let(
        EventDispatcher $dispatcher,
        ProcessorInterface $processor
    )
    {
        $this->beAnInstanceOf(TestBehatEventListener::class);
        $this->beConstructedWith($dispatcher, $processor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BehatEventListener::class);
    }

    function it_should_listen_to_behat_events()
    {
        $this->getSubscribedEvents()->shouldHaveKey(ExerciseCompleted::BEFORE);
        $this->getSubscribedEvents()->shouldHaveKey(ExerciseCompleted::AFTER);
    }

    function it_should_dispatch_coverage_refresh_event(
        EventDispatcher $dispatcher,
        ProcessorInterface $processor
    )
    {
        $processor->clear()->shouldBeCalled();

        $dispatcher
            ->dispatch(Argument::type(CoverageEvent::class), CoverageEvent::BEFORE_REFRESH)
            ->shouldBeCalled();
        $dispatcher
            ->dispatch(Argument::type(CoverageEvent::class), CoverageEvent::REFRESH)
            ->shouldBeCalled();

        $this->refreshCoverage();
    }

    function it_should_dispatch_coverage_start_event(
        EventDispatcher $dispatcher,
        ScenarioTested $scope,
        ScenarioInterface $scenario,
        FeatureNode $feature,
        TestResult $results,
        ProcessorInterface $processor
    )
    {
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

    function it_should_dispatch_coverage_stop_event(
        EventDispatcher $dispatcher,
        AfterTested $afterTested,
        TestResult $result,
        TestCase $testCase,
        CoverageEvent $coverageEvent,
        ProcessorInterface $processor
    )
    {
        $afterTested->getTestResult()->willReturn($result)->shouldBeCalledOnce();
        $result->getResultCode()->willReturn(0)->shouldBeCalledOnce();

        $coverageEvent->getTestCase()->shouldBeCalled()->willReturn($testCase);
        $testCase->setResult(0)->shouldBeCalled();
        $processor->addTestCase($testCase)->shouldBeCalled();
        $processor->stop()->shouldBeCalled();

        $dispatcher->dispatch(Argument::type(CoverageEvent::class), CoverageEvent::BEFORE_STOP)
            ->shouldBeCalled();
        $dispatcher->dispatch(Argument::type(CoverageEvent::class), CoverageEvent::STOP)
            ->shouldBeCalled();

        $this->setCoverageEvent($coverageEvent);
        $this->stopCoverage($afterTested);
    }

    function it_should_dispatch_report_events(
        EventDispatcher $dispatcher,
        ProcessorInterface $processor
    )
    {
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
