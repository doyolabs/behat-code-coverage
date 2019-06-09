<?php

namespace spec\Doyo\Behat\Coverage\Console;

use Doyo\Behat\Coverage\Console\IO;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class IOSpec extends ObjectBehavior
{
    function let(
        InputInterface $input,
        OutputInterface $output,
        OutputFormatterInterface $formatter
    )
    {
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->getFormatter()->willReturn($formatter);
        $this->beConstructedWith($input, $output);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IO::class);
    }

    function its_has_error_should_be_mutable()
    {
        $this->hasError()->shouldBe(false);
        $this->setHasError(true);
        $this->hasError()->shouldBe(true);
    }

    function its_sessionError_should_render_error_message(
        OutputInterface $output
    )
    {
        $output->writeln(Argument::cetera())
            ->shouldBeCalledOnce();

        $this->sessionError('some', 'error');
    }
}
