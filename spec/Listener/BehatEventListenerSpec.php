<?php

namespace spec\Doyo\Behat\Coverage\Listener;

use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
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
        CoverageEvent $event,
        TestCase $testCase
    )
    {
        $this->beAnInstanceOf(TestBehatEventListener::class);
        $this->beConstructedWith($dispatcher);
        $event->beConstructedWith([$testCase->getWrappedObject()]);
        $this->setCoverageEvent($event);
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
        CoverageEvent $event
    )
    {
        $event->setTestCase(null)->shouldBeCalled();
        $event->setCoverage(Argument::type('array'))->shouldBeCalled();

        $dispatcher
            ->dispatch(Argument::type(RefreshEvent::class), CoverageEvent::BEFORE_REFRESH)
            ->shouldBeCalled();
        $dispatcher
            ->dispatch(Argument::type(RefreshEvent::class), CoverageEvent::REFRESH)
            ->shouldBeCalled();

        $this->refreshCoverage();
    }

    function it_should_dispatch_coverage_start_event(
        EventDispatcher $dispatcher,
        ScenarioTested $scope,
        ScenarioInterface $scenario,
        FeatureNode $feature,
        CoverageEvent $event,
        TestResult $results
    )
    {
        $scope->getFeature()->willReturn($feature);
        $scope->getScenario()->willReturn($scenario);
        $feature->getFile()->willReturn('some.feature');
        $scenario->getLine()->willReturn('line');
        $results->getResultCode()->willReturn(TestResults::PASSED);

        $event->setTestCase(Argument::type(TestCase::class))->shouldBeCalled();
        $dispatcher
            ->dispatch($event, CoverageEvent::BEFORE_START)
            ->shouldBeCalled();
        $dispatcher
            ->dispatch($event, CoverageEvent::START)
            ->shouldBeCalled();

        $this->startCoverage($scope);
    }

    function it_should_dispatch_coverage_stop_event(
        EventDispatcher $dispatcher,
        CoverageEvent $event,
        AfterTested $afterTested,
        TestResult $result,
        TestCase $testCase
    )
    {
        $dispatcher->dispatch($event, CoverageEvent::BEFORE_STOP)
            ->shouldBeCalled();
        $dispatcher->dispatch($event, CoverageEvent::STOP)
            ->shouldBeCalled();

        $afterTested->getTestResult()->willReturn($result)->shouldBeCalledOnce();
        $result->getResultCode()->willReturn(0)->shouldBeCalledOnce();
        $event->getTestCase()->willReturn($testCase)->shouldBeCalledOnce();
        $testCase->setResult(0)->shouldBeCalledOnce();

        $this->stopCoverage($afterTested);
    }

    function it_should_dispatch_report_events(
        EventDispatcher $dispatcher
    )
    {
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
