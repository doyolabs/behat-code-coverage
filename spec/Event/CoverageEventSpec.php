<?php

namespace spec\Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Bridge\Aggregate;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Webmozart\Assert\Assert;

class CoverageEventSpec extends ObjectBehavior
{
    function let(
        ProcessorInterface $processor,
        ConsoleIO $consoleIO,
        TestCase $testCase
    ){
        $this->beConstructedWith($processor, $consoleIO, $testCase);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CoverageEvent::class);
    }

    function its_properties_should_be_mutable(
        ProcessorInterface $processor,
        ConsoleIO $consoleIO,
        TestCase $testCase
    )
    {
        $this->getProcessor()->shouldReturn($processor);
        $this->getConsoleIO()->shouldReturn($consoleIO);
        $this->getTestCase()->shouldReturn($testCase);
    }
}
