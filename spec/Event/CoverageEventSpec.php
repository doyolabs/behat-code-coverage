<?php

namespace spec\Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Bridge\Aggregate;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CoverageEventSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('some-id');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CoverageEvent::class);
    }
}
