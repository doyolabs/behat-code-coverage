<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\CachedCoverage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CachedCoverageSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CachedCoverage::class);
    }
}
