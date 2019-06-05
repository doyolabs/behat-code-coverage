<?php

namespace spec\Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Event\ReportEvent;
use Doyo\Behat\Coverage\Exception\ReportProcessException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\Doyo\Behat\Coverage\CoverageHelperTrait;
use Symfony\Component\Console\Style\StyleInterface;

class ReportEventSpec extends ObjectBehavior
{
    use CoverageHelperTrait;

    function it_is_initializable()
    {
        $this->shouldHaveType(ReportEvent::class);
    }

    function its_coverage_should_be_mutable($driver)
    {
        $this->getDriverSubject($driver);
        $coverage = $this->getCoverageSubject($driver);

        $this->setCoverage($coverage)->shouldReturn($this);
        $this->getCoverage()->shouldReturn($coverage);
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
