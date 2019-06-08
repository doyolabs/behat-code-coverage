<?php

namespace spec\Doyo\Behat\Coverage\Listener;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\SessionInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Listener\LocalCoverageListener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocalCoverageListenerSpec extends ObjectBehavior
{
    function let(
        SessionInterface $session,
        ProcessorInterface $processor
    )
    {
        $filter = new Filter();
        $session->setProcessor($processor);
        $this->beConstructedWith($session, [], $filter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocalCoverageListener::class);
    }

    function it_should_subscribe_to_coverage_events()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::REFRESH);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::START);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::COMPLETED);
    }

    function it_should_handle_coverage_refresh_event(
        SessionInterface $session
    )
    {
        $session->reset()->shouldBeCalledOnce();
        $this->coverageRefresh();
    }

    function it_should_handle_coverage_start_event(
        SessionInterface $session,
        CoverageEvent $event,
        TestCase $testCase
    )
    {
        $event->getTestCase()->shouldBeCalledOnce()->willReturn($testCase);
        $session->setTestCase($testCase)->shouldBeCalledOnce();
        $session->save()->shouldBeCalledOnce();

        $this->coverageStarted($event);
    }

    function it_should_handle_coverage_completed_event(
        SessionInterface $session,
        ProcessorInterface $processor,
        CoverageEvent $event
    )
    {
        $session->refresh()->shouldBeCalled();
        $session->getProcessor()->willReturn($processor);
        $event->getProcessor()->willReturn($processor);

        $processor->merge($processor)->shouldBeCalled();

        $this->coverageCompleted($event);
    }
}
