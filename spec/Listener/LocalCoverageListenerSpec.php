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

use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Listener\LocalCoverageListener;
use Doyo\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Bridge\CodeCoverage\Session\SessionInterface;
use Doyo\Bridge\CodeCoverage\TestCase;
use PhpSpec\ObjectBehavior;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocalCoverageListenerSpec extends ObjectBehavior
{
    public function let(
        SessionInterface $session,
        ProcessorInterface $processor
    ) {
        $filter = new Filter();
        $session->setProcessor($processor);
        $session->refresh()->willReturn(null);
        $this->beConstructedWith($session, [], $filter);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LocalCoverageListener::class);
    }

    public function it_should_subscribe_to_coverage_events()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::REFRESH);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::START);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::COMPLETED);
    }

    public function it_should_handle_coverage_refresh_event(
        SessionInterface $session
    ) {
        $session->reset()->shouldBeCalledOnce();
        $this->coverageRefresh();
    }

    public function it_should_handle_coverage_start_event(
        SessionInterface $session,
        CoverageEvent $event,
        TestCase $testCase
    ) {
        $event->getTestCase()->shouldBeCalledOnce()->willReturn($testCase);
        $session->setTestCase($testCase)->shouldBeCalledOnce();
        $session->save()->shouldBeCalledOnce();

        $this->coverageStarted($event);
    }

    public function it_should_handle_coverage_completed_event(
        SessionInterface $session,
        ProcessorInterface $processor,
        CoverageEvent $event,
        ConsoleIO $consoleIO
    ) {
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
