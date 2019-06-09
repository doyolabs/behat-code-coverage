<?php

namespace spec\Doyo\Behat\Coverage\Listener;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\SessionInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Listener\LocalCoverageListener;
use Doyo\Behat\Coverage\Console\ConsoleIO;
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
        $session->refresh()->willReturn(null);
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
        CoverageEvent $event,
        ConsoleIO $consoleIO
    )
    {
        $e = new \Exception('some error');

        $session->refresh()->shouldBeCalled();
        $session->getProcessor()->willReturn($processor);
        $session->hasExceptions()->willReturn(true);
        $session->getExceptions()->willReturn([$e]);
        $session->getName()->shouldBeCalledOnce()->willReturn('some-session');
        $consoleIO->sessionError('some-session', 'some error')->shouldBeCalledOnce();

        $event->getConsoleIO()->willReturn($consoleIO);
        $event->getProcessor()->willReturn($processor);

        $processor->merge($processor)->shouldBeCalled();

        $this->coverageCompleted($event);
    }
}
