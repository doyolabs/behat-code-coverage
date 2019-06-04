<?php

namespace spec\Doyo\Behat\Coverage\Bridge;

use Doyo\Behat\Coverage\Bridge\Compat;
use Doyo\Behat\Coverage\Bridge\Report;
use Doyo\Behat\Coverage\Bridge\Report\ReportInterface;
use Doyo\Behat\Coverage\Event\ReportEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;
class ReportSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Report::class);
    }

    function it_should_subscribe_to_report_process_event()
    {
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(ReportEvent::PROCESS,'onReportProcess');
    }

    function its_processor_should_be_mutable(
        Clover $report
    )
    {
        $this->setProcessor($report)->shouldReturn($this);
        $this->getProcessor()->shouldReturn($report);
    }

    function its_name_should_be_mutable()
    {
        $this->setName('some')->shouldReturn($this);
        $this->getName()->shouldReturn('some');
    }

    function its_target_should_be_mutable()
    {
        $this->setTarget('some')->shouldReturn($this);
        $this->getTarget()->shouldReturn('some');
    }

    function it_should_handle_report_process_event(
        ReportEvent $event,
        Clover $report,
        $driver
    )
    {
        $driver->beADoubleOf(Compat::getDriverClass('Dummy'));
        $coverage = new CodeCoverage($driver->getWrappedObject());

        $event->getCoverage()->willReturn($coverage)->shouldBeCalled();

        $report->process($coverage, 'some-target', 'some-name')->shouldBeCalled();

        $this->setTarget('some-target');
        $this->setName('some-name');
        $this->setProcessor($report);

        $this->onReportProcess($event);

    }
}
