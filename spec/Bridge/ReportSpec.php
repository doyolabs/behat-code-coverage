<?php

namespace spec\Doyo\Behat\Coverage\Bridge;

use Behat\Mink\Driver\DriverInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\Report;
use Doyo\Behat\Coverage\Event\ReportEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Report\Clover;
use Symfony\Component\Console\Style\StyleInterface;

class ReportSpec extends ObjectBehavior
{
    private $coverage;

    function let(
        Driver $driver,
        Processor $processor
    )
    {
        $processor->beConstructedWith([$driver->getWrappedObject()]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Report::class);
    }

    function it_should_subscribe_to_report_process_event()
    {
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(ReportEvent::PROCESS,'onReportProcess');
    }

    function its_processor_should_be_mutable(
        TestReportProcessor $report
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
        TestReportProcessor $report,
        StyleInterface $io,
        Processor $processor,
        DriverInterface $driver
    )
    {
        $coverage = new CodeCoverage($driver->getWrappedObject());
        $event->getIO()->willReturn($io);
        $event->getProcessor()->willReturn($processor)->shouldBeCalled();
        $processor->getCodeCoverage()->willReturn($coverage);

        $report->process(Argument::type(CodeCoverage::class), 'some-target', 'some-name')->shouldBeCalled();

        $this->setTarget('some-target');
        $this->setName('some-name');
        $this->setProcessor($report);

        $this->onReportProcess($event);

    }
}
