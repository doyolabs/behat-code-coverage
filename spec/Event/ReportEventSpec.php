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

namespace spec\Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Doyo\Bridge\CodeCoverage\ProcessorInterface;
use PhpSpec\ObjectBehavior;

class ReportEventSpec extends ObjectBehavior
{
    public function let(
        ProcessorInterface $processor,
        ConsoleIO $consoleIO
    ) {
        $this->beConstructedWith($processor, $consoleIO);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ReportEvent::class);
    }

    public function its_properties_should_be_mutable(
        ProcessorInterface $processor,
        ConsoleIO $consoleIO
    ) {
        $this->getProcessor()->shouldReturn($processor);
        $this->getConsoleIO()->shouldReturn($consoleIO);
    }
}
