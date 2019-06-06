<?php

namespace spec\Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Doyo\Behat\Coverage\Exception\ReportProcessException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\Doyo\Behat\Coverage\CoverageHelperTrait;
use Symfony\Component\Console\Style\StyleInterface;

class ReportEventSpec extends ObjectBehavior
{
    function let(
        Processor $processor
    )
    {
        $this->setProcessor($processor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReportEvent::class);
    }

    function its_processor_should_be_mutable(
        Processor $processor
    )
    {
        $this->setProcessor($processor)->shouldReturn($this);
        $this->getProcessor()->shouldReturn($processor);
    }

    function its_exceptions_should_be_mutable(
        ReportProcessException $exception
    )
    {
        $this->addException($exception);
        $this->getExceptions()->shouldHaveCount(1);
    }

    function its_IO_should_be_mutable(
        StyleInterface $style
    )
    {
        $this->setIO($style)->shouldReturn($this);
        $this->getIO()->shouldReturn($style);
    }
}
