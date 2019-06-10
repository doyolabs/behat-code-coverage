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

namespace spec\Doyo\Behat\Coverage\Bridge;

use Behat\Mink\Driver\DriverInterface;
use Doyo\Behat\Coverage\Bridge\Report;
use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Doyo\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Bridge\CodeCoverage\Processor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\CodeCoverage;

class ReportSpec extends ObjectBehavior
{
    public function let(
        Dummy $driver,
        Processor $processor,
        ReportEvent $event,
        ConsoleIO $consoleIO
    ) {
        $coverage = new CodeCoverage($driver->getWrappedObject());
        $processor->beConstructedWith([$driver->getWrappedObject()]);
        $event->getProcessor()->willReturn($processor);
        $processor->getCodeCoverage()->willReturn($coverage);
        $event->getConsoleIO()->willReturn($consoleIO);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Report::class);
    }

    public function it_should_subscribe_to_report_process_event()
    {
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(ReportEvent::PROCESS, 'onReportProcess');
    }

    public function its_processor_should_be_mutable(
        TestReportProcessor $report
    ) {
        $this->setReportProcessor($report)->shouldReturn($this);
        $this->getReportProcessor()->shouldReturn($report);
    }

    public function its_name_should_be_mutable()
    {
        $this->setName('some')->shouldReturn($this);
        $this->getName()->shouldReturn('some');
    }

    public function its_target_should_be_mutable()
    {
        $this->setTarget('some')->shouldReturn($this);
        $this->getTarget()->shouldReturn('some');
    }

    public function it_should_handle_report_process_event(
        ReportEvent $event,
        TestReportProcessor $report,
        ConsoleIO $io,
        Processor $processor,
        DriverInterface $driver
    ) {
        $coverage = new CodeCoverage($driver->getWrappedObject());
        $event->getConsoleIO()->willReturn($io);
        $event->getProcessor()->willReturn($processor)->shouldBeCalled();
        $processor->getCodeCoverage()->willReturn($coverage);

        $report->process(Argument::type(CodeCoverage::class), 'some-target', 'some-name')->shouldBeCalled();

        $this->setTarget('some-target');
        $this->setName('some-name');
        $this->setReportProcessor($report);

        $this->onReportProcess($event);
    }

    public function it_should_handle_error_when_creating_report(
        TestReportProcessor $testReportProcessor,
        ReportEvent $event,
        Processor $processor,
        ConsoleIO $consoleIO
    ) {
        $e = new \Exception('Report Error');

        $event->getProcessor()->willReturn($processor);
        $consoleIO->error(Argument::containingString('Report Error'))
            ->shouldBeCalledOnce();

        $testReportProcessor->process(Argument::any(), Argument::any(), Argument::any())
            ->willThrow($e);
        $this->setReportProcessor($testReportProcessor);
        $this->onReportProcess($event);
    }
}
