<?php

namespace spec\Doyo\Behat\Coverage\Controller\Cli;

use Doyo\Behat\Coverage\Bridge\Symfony\Event;
use Doyo\Behat\Coverage\Controller\Cli\CoverageController;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

    function it_should_subscribe_to_coverage_before_event()
    {
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(CoverageEvent::BEFORE_START, 'validateEvent');
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(CoverageEvent::BEFORE_STOP, 'validateEvent');
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(CoverageEvent::BEFORE_REFRESH, 'validateEvent');
    }

    function it_should_subscribe_to_report_event()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(ReportEvent::BEFORE_PROCESS,'onBeforeReportProcess');
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(ReportEvent::PROCESS,['validateEvent',100]);
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(ReportEvent::AFTER_PROCESS,['validateEvent','onAfterReportProcess']);
    }

    function it_should_handle_before_report_process_event(
        ReportEvent $event,
        StyleInterface $style
    )
    {
        $event->setIO($style)->shouldBeCalled();

        $this->onBeforeReportProcess($event);
    }

    function it_should_validate_coverage_and_report_events(
        InputInterface $input,
        OutputInterface $output,
        Event $event
    )
    {
        $this->decorateCoverageDisabled($input, $output);
        $event->stopPropagation()->shouldBeCalledOnce();
        $this->validateEvent($event);

        $this->decorateCoverageEnabled($input, $output);
        $this->validateEvent($event);
    }

    function decorateCoverageEnabled($input, $output)
    {
        $input->hasParameterOption(['--coverage'])->willReturn(true);
        $this->execute($input, $output);
    }

    function decorateCoverageDisabled($input, $output)
    {
        $input->hasParameterOption(['--coverage'])->willReturn(false);
        $this->execute($input, $output);
    }
}
