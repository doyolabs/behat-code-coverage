<?php

namespace spec\Doyo\Behat\Coverage;

use Doyo\Behat\Coverage\Extension;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;

class ExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Extension::class);
    }

    function it_should_be_a_behat_extension()
    {
        $this->shouldImplement(ExtensionInterface::class);
    }
}
