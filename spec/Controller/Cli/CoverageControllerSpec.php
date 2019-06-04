<?php

namespace spec\Doyo\Behat\Coverage\Controller\Cli;

use Doyo\Behat\Coverage\Controller\Cli\CoverageController;
use Doyo\Behat\Coverage\Event\ReportEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoverageControllerSpec extends ObjectBehavior
{
    function let(
        StyleInterface $style
    )
    {
        $this->beConstructedWith($style);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CoverageController::class);
    }

    function it_should_subscribe_to_report_event()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(ReportEvent::BEFORE_PROCESS,'onBeforeReportProcess');
    }

    function it_should_handle_before_report_process_event(
        ReportEvent $event,
        StyleInterface $style
    )
    {
        $event->setIO($style)->shouldBeCalled();

        $this->onBeforeReportProcess($event);
    }
}
