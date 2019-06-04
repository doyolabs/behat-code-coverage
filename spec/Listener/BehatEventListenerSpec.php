<?php

namespace spec\Doyo\Behat\Coverage\Listener;

use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Doyo\Behat\Coverage\Bridge\Aggregate;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\RefreshEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Doyo\Behat\Coverage\Listener\BehatEventListener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

class BehatEventListenerSpec extends ObjectBehavior
{
    function let(
        EventDispatcherInterface $dispatcher,
        CoverageEvent $event
    )
    {
        $this->beAnInstanceOf(TestBehatEventListener::class);
        $this->beConstructedWith($dispatcher);
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
        EventDispatcherInterface $dispatcher,
        CoverageEvent $event
    )
    {
        $event->setCoverageId(null)->shouldBeCalled();
        $event->setAggregate(Argument::type(Aggregate::class))->shouldBeCalled();

        $dispatcher
            ->dispatch(CoverageEvent::REFRESH,Argument::type(RefreshEvent::class))
            ->shouldBeCalled();

        $this->refreshCoverage();
    }

    function it_should_dispatch_coverage_start_event(
        EventDispatcherInterface $dispatcher,
        ScenarioScope $scope,
        ScenarioInterface $scenario,
        FeatureNode $feature,
        CoverageEvent $event
    )
    {
        $scope->getFeature()->willReturn($feature);
        $scope->getScenario()->willReturn($scenario);
        $feature->getFile()->willReturn('some.feature');
        $scenario->getLine()->willReturn('line');

        $event->setCoverageId('some.feature:line')->shouldBeCalled();
        $dispatcher
            ->dispatch(CoverageEvent::START, $event)
            ->shouldBeCalled();

        $this->startCoverage($scope);
    }

    function it_should_dispatch_coverage_stop_event(
        EventDispatcherInterface $dispatcher,
        CoverageEvent $event
    )
    {
        $dispatcher->dispatch(CoverageEvent::STOP, $event)
            ->shouldBeCalled();

        $this->stopCoverage();
    }

    function it_should_dispatch_report_events(
        EventDispatcherInterface $dispatcher
    )
    {
        $dispatcher
            ->dispatch(ReportEvent::BEFORE_PROCESS, Argument::any())
            ->shouldBeCalled();
        $dispatcher
            ->dispatch(ReportEvent::PROCESS, Argument::any())
            ->shouldBeCalled();
        $dispatcher
            ->dispatch(ReportEvent::AFTER_PROCESS, Argument::any())
            ->shouldBeCalled();

        $this->generateReport();
    }
}
