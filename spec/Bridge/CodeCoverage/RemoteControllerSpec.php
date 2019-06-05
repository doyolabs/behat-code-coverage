<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\RemoteController;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RemoteControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RemoteController::class);
    }
}
