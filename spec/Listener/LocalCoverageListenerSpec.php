<?php

namespace spec\Doyo\Behat\Coverage\Listener;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\SessionInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Bridge\Exception\CacheException;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Listener\LocalCoverageListener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocalCoverageListenerSpec extends ObjectBehavior
{
    function let(
        SessionInterface $session
    )
    {
        $filter = new Filter();
        $session->setCodeCoverageOptions(Argument::any())->willReturn(null);
        $session->setFilterOptions(Argument::any())->willReturn(null);
        $session->save()->willReturn(null);
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
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::STOP);
    }

    function it_should_handle_coverage_refresh_event(
        SessionInterface $session
    )
    {
        $session->reset()->shouldBeCalledOnce();
        $this->onCoverageRefresh();
    }

    function it_should_handle_coverage_start_event(
        SessionInterface $session,
        CoverageEvent $event,
        TestCase $testCase
    )
    {
        $event->getTestCase()->shouldBeCalledOnce()->willReturn($testCase);
        $session->setTestCase($testCase)->shouldBeCalledOnce();
        $session->save()->shouldBeCalledTimes(2);

        $this->onCoverageStarted($event);
    }

    function it_should_handle_on_coverage_stop_event(
        CoverageEvent $event,
        SessionInterface $session
    )
    {
        $data = ['onCoverageStop'];
        $session->refresh()->shouldBeCalled();
        $session->getData()->shouldBeCalled()->willReturn($data);
        $session->hasExceptions()->willReturn(false);
        $event->updateCoverage($data)->shouldBeCalled();

        $this->onCoverageStopped($event);

        $session->hasExceptions()->willReturn(true);
        $session->getExceptions()->willReturn(['Test Message']);

        $this
            ->shouldThrow(CacheException::class)
            ->during('onCoverageStopped',[$event]);
    }
}
