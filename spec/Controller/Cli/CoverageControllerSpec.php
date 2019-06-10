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

namespace spec\Doyo\Behat\Coverage\Controller\Cli;

use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Behat\Coverage\Controller\Cli\CoverageController;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Doyo\Symfony\Bridge\EventDispatcher\Event;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoverageControllerSpec extends ObjectBehavior
{
    public function let(
        ReportEvent $reportEvent,
        ConsoleIO $consoleIO
    ) {
        $reportEvent->getConsoleIO()->willReturn($consoleIO);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CoverageController::class);
    }

    public function it_should_add_coverage_option_during_configure(
        Command $command
    ) {
        $command->addOption(Argument::cetera())->shouldBeCalled();

        $this->configure($command);
    }

    public function it_should_subscribe_to_coverage_before_event()
    {
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::BEFORE_START);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::BEFORE_STOP);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::BEFORE_REFRESH);
    }

    public function it_should_subscribe_to_report_event()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->getSubscribedEvents()->shouldHaveKey(ReportEvent::BEFORE_PROCESS);
        $this->getSubscribedEvents()->shouldHaveKey(ReportEvent::AFTER_PROCESS);
    }

    public function it_should_validate_coverage_and_report_events(
        InputInterface $input,
        OutputInterface $output,
        Event $event
    ) {
        $this->decorateCoverageDisabled($input, $output);
        $event->stopPropagation()->shouldBeCalledOnce();
        $this->validateEvent($event);

        $this->decorateCoverageEnabled($input, $output);
        $this->validateEvent($event);
    }

    public function decorateCoverageEnabled($input, $output)
    {
        $input->hasParameterOption(['--coverage'])->willReturn(true);
        $this->execute($input, $output);
    }

    public function decorateCoverageDisabled($input, $output)
    {
        $input->hasParameterOption(['--coverage'])->willReturn(false);
        $this->execute($input, $output);
    }

    public function it_should_handle_before_report_events(
        ReportEvent $reportEvent,
        ConsoleIO $consoleIO
    ) {
        $consoleIO
            ->section(Argument::containingString('generating'))
            ->shouldBeCalledOnce();

        $this->beforeReportProcess($reportEvent);
    }

    public function it_should_handle_after_report_events(
        ReportEvent $reportEvent,
        ConsoleIO $consoleIO
    ) {
        $consoleIO->success(Argument::any())->shouldBeCalledOnce();
        $consoleIO->error(Argument::any())->shouldBeCalledOnce();

        $consoleIO->hasError()->willReturn(false);
        $this->afterReportProcess($reportEvent);

        $consoleIO->hasError()->willReturn(true);
        $this->afterReportProcess($reportEvent);
    }
}
