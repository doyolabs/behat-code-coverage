<?php

namespace spec\Doyo\Behat\Coverage\Bridge;

use Doyo\Behat\Coverage\Remote\CoverageRepository;
use Doyo\Behat\Coverage\Bridge\Compat;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\RemoteCoverage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Version;


class RemoteCoverageSpec extends ObjectBehavior
{
    function let(CoverageRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoteCoverage::class);
    }
}
