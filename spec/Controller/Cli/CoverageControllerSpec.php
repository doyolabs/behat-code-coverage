<?php

namespace spec\Doyo\Behat\Coverage\Controller\Cli;

use Doyo\Behat\Coverage\Bridge\Symfony\Event;
use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Behat\Coverage\Controller\Cli\CoverageController;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoverageControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CoverageController::class);
    }

    function it_should_add_coverage_option_during_configure(
        Command $command
    )
    {
        $command->addOption(Argument::cetera())->shouldBeCalled();

        $this->configure($command);
    }

    function it_should_subscribe_to_coverage_before_event()
    {
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::BEFORE_START);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::BEFORE_STOP);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::BEFORE_REFRESH);
    }

    function it_should_subscribe_to_report_event()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->getSubscribedEvents()->shouldHaveKey(ReportEvent::BEFORE_PROCESS);
        $this->getSubscribedEvents()->shouldHaveKey(ReportEvent::AFTER_PROCESS);
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
