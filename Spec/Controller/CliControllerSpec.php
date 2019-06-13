<?php

/*
 * This file is part of the doyo/code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spec\Doyo\Behat\CodeCoverage\Controller;

use Behat\Testwork\Cli\Controller;
use Doyo\Behat\CodeCoverage\Controller\CliController;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Command\Command;

class CliControllerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(CliController::class);
    }

    public function it_should_be_a_behat_cli_controller()
    {
        $this->shouldImplement(Controller::class);
    }

    public function it_should_add_coverage_option(
        Command $command
    ) {
        $command
            ->addOption('coverage', Argument::cetera())
            ->shouldBeCalledOnce();

        $this->configure($command);
    }
}
