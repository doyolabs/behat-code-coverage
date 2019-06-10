<?php

/*
 * This file is part of the doyo/behat-coverage-extension project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use PhpSpec\ObjectBehavior;

class CoverageEventSpec extends ObjectBehavior
{
    public function let(
        ProcessorInterface $processor,
        ConsoleIO $consoleIO,
        TestCase $testCase
    ) {
        $this->beConstructedWith($processor, $consoleIO, $testCase);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CoverageEvent::class);
    }

    public function its_properties_should_be_mutable(
        ProcessorInterface $processor,
        ConsoleIO $consoleIO,
        TestCase $testCase
    ) {
        $this->getProcessor()->shouldReturn($processor);
        $this->getConsoleIO()->shouldReturn($consoleIO);
        $this->getTestCase()->shouldReturn($testCase);
    }
}
