<?php

namespace spec\Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Doyo\Behat\Coverage\Exception\ReportProcessException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\Doyo\Behat\Coverage\CoverageHelperTrait;
use Symfony\Component\Console\Style\StyleInterface;

class ReportEventSpec extends ObjectBehavior
{
    function let(
        ProcessorInterface $processor,
        ConsoleIO $consoleIO
    )
    {
        $this->beConstructedWith($processor, $consoleIO);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReportEvent::class);
    }

    function its_properties_should_be_mutable(
        ProcessorInterface $processor,
        ConsoleIO $consoleIO
    )
    {
        $this->getProcessor()->shouldReturn($processor);
        $this->getConsoleIO()->shouldReturn($consoleIO);
    }
}
